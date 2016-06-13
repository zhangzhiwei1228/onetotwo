<?php

class Order extends Abstract_Model
{
	protected $_name = 'order';
	protected $_primary = 'id';

	protected $_referenceMap = array(
		'buyer' => array(
			'class' => 'User',
			'type' => 'hasone',
			'source' => 'buyer_id',
			'target' => 'id'
		),
		'seller' => array(
			'class' => 'User',
			'type' => 'hasone',
			'source' => 'seller_id',
			'target' => 'id'
		),
		'delivery' => array(
			'class' => 'Order_Delivery',
			'type' => 'hasone',
			'source' => 'id',
			'target' => 'order_id'
		),
		'goods' => array(
			'class' => 'Order_Goods',
			'type' => 'hasmany',
			'source' => 'id',
			'target' => 'order_id'
		)
	);

	public function outputFilter($data)
	{
		if (isset($data['status'])) {
			switch($data['status']) {
				case 1:
					$data['status_text'] = '等待付款';
					break;
				case 2:
					$data['status_text'] = '正在发货';
					break;
				case 3:
					$data['status_text'] = '已发货';
					break;
				case 4:
					$data['status_text'] = '已完成';
					break;
				case 0:
					$data['status_text'] = '已关闭';
					break;
			}
		}

		return $data;
	}

	public function getByCode($code)
	{
		return $this->select()
			->where('code = ?', $code)
			->fetchRow();
	}

	public function deleteById($id)
	{
		M('Order_Goods')->delete('order_id = '.(int)$id);
		M('Order_Return')->delete('order_id = '.(int)$id);
		M('Order_Delivery')->delete('order_id = '.(int)$id);
		return parent::deleteById($id);
	}

	public function process($uid = 0)
	{
		//处理超时订单
		$select = M('Order')->select();
		$select->where('status != 4 AND status != 0 AND expiry_time != 0 AND expiry_time < ?', time());
		if ($uid) { //根据用户处理
			$select->where('(buyer_id = ? OR seller_id = ?)', (int)$uid);
		}

		$order = $select->fetchRows();

		foreach ($order as $row) {
			switch ($row['status']) {
				case 1: //付款超时
					$row->cancel(date(DATETIME_FORMAT)." - 付款超时，系统自动关闭交易。 \r\n");
					break;
				case 2: //发货超时
					$row->cancel(date(DATETIME_FORMAT)." - 发货超时，系统自动关闭交易。并退款给买家。 \r\n");
					break;
				case 3: //签收超时
					$row->confirm(date(DATETIME_FORMAT)." - 签收超时，系统自动完成交易。并支付货款给卖家。 \r\n");
					break;
			}
		}
	}

	public function pay($order, $virtual = 0)
	{
		//跳过不是未付款及超时订单
		if ($order['status'] != 1) {
			throw new App_Exception("请勿重复支付，当前订单已支付", 2);
		}


		$this->getAdapter()->beginTrans();
		try {
			//使用优惠券
			if ($order['coupon_code']) {
				$coupon = M('Coupon_Receive')->select()
					->where('code = ?', $order['coupon_code'])
					->fetchRow();
				if (!$coupon->exists()) {
					throw App_Exception('支付失败，优惠券已失效');
				}

				$coupon->user_id = $order['buyer_id'];
				$coupon->used_time = time();
				$coupon->is_used = 1;
				$coupon->save();
			}

			if (!$virtual && $order['total_pay_amount'] > 0) { //非虚拟订单，资金处理
				$voucher = 'TS-'.$order['code'];
				//从用户帐户扣款
				if ($order['total_pay_amount'] && $order['buyer_id']) { //非会员忽略资金处理
					$order->buyer->expend('pay', $order['total_pay_amount'], $voucher, '订单号#'.$voucher)
						->commit();
				}
			}

			$order->status = 2;
			$order->is_virtual = $virtual;
			$order->pay_time = time();
			$order->expiry_time = time()+(int)M('Setting')->timeout_delivery;
			$order->save();

			//加积分
			// if ($order['total_earn_points']) {
			// 	$order->buyer->credit($order['total_earn_points'], '消费'.$order['total_amount'].'元，赠送积分'.$order['total_earn_points'].'点');
			// 	if ($order->buyer['referrals_id']) { //给推荐人加积分
			// 		$point = ($order['total_earn_points']*0.05);
			// 		$order->buyer->credit($point, '您推荐的会员消费'.$order['total_amount'].'元，赠送积分'.$point.'点');
			// 	}
			// }

			//扣免费积分
			if ($order['total_credit']) {
				$order->buyer->credit($order['total_credit']*-1, '消耗'.$order['total_credit'].'点免费积分');
			}
			//扣快乐积分
			if ($order['total_credit_happy']) {
				$order->buyer->creditHappy($order['total_credit_happy']*-1, '消耗'.$order['total_credit_happy'].'点快乐积分');
			}
			//扣积分币
			if ($order['total_credit_coin']) {
				$order->buyer->creditCoin($order['total_credit_coin']*-1, '消耗'.$order['total_credit_coin'].'点积分币');
			}

			//修改相关发票状态为待开票
			M('Invoice')->update('status = 1', 'order_ids = '.(int)$order['id']);

			$this->getAdapter()->commit();

			//消息通知
			if ($order->seller_id) {
				$url = H('url', 'module=usercp&controller=order&action=list&view=shiped');
				M('Message')->send($order->seller_id, '恭喜，您有一笔新订单已付款。 <br><a href="'.$url.'">去看看</a>');
			} else {
				$url = H('url', 'module=admincp&controller=order&action=list&view=shiped');
				M('Message')->send(-1, '恭喜，来自'.$order->buyer->username.'的新订单等待发货。 <br><a href="'.$url.'">去看看</a>');
			}

		} catch (App_Exception $e) {
			echo $e->dump();
			$this->getAdapter()->rollback();
		}
	}

	public function delivery($order, $code, $remark)
	{
		$this->getAdapter()->beginTrans();
		try {
			M('Order_Delivery')->insert(array(
				'code' => $code,
				'remark' => $remark,
				'order_id' => $order->id,
				'shipping_id' => $order->shipping_id,
			));

			$order->status = 3;
			$order->delivery_time = time();
			$order->expiry_time = time() + (int)M('Setting')->timeout_confirm;
			$order->save();
			$this->getAdapter()->commit();
		} catch (Suco_Exception $e) {
			$this->getAdapter()->rollback();
		}
	}

	/**
	 * 取消订单
	 */
	public function cancel($order, $log = '')
	{
		if ($order->status == 1 || $order->status == 2) {
			$this->getAdapter()->beginTrans();
			try {
				//退款给买家
				if ($order->status == 2 && $order->buyer->exists()) {
					$order->buyer->income('refund', $order->total_pay_amount, 'TS-'.$order->code, '订单取消，自动退款')
						->commit();
				}

				//关闭订单
				$order->status = 0;
				//释放库存
				foreach($order->goods as $row) {
					$goods = M('Goods')->getById($row['goods_id']);
					$goods->quantity += $row['purchase_quantity'];
					$goods->save();

					$sku = M('Goods_Sku')->getById($row['sku_id']);
					$sku->quantity += $row['purchase_quantity'];
					$sku->save();
				}
				if ($log) {
					$order->logs .= $log;
				}
				$order->save();
				$this->getAdapter()->commit();
			} catch (Suco_Exception $e) {
				$this->getAdapter()->rollback();
			}
		}
	}

	/**
	 * 确认收货
	 */
	public function confirm($order, $log)
	{
		$this->getAdapter()->beginTrans();
		try {
			if ($order->seller->exists()) {
				//给供应商付款
				$order->seller->income('income', $order->total_cost, 'TS-'.$order->id, '交易成功，收取货款')
					->commit();
			}

			$order->status = 4;
			$order->confirm_time = time();
			$order->expiry_time = time() + (int)M('Setting')->timeout_comment;
			if ($log) {
				$order->logs .= $log;
			}
			$order->save();
			$this->getAdapter()->commit();
		} catch (Suco_Exception $e) {
			$this->getAdapter()->rollback();
		}
	}
}
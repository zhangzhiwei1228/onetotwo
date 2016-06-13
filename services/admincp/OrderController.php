<?php

class Admincp_OrderController extends Admincp_Controller_Action
{
	public function init()
	{
		$this->_auth();
	}

	public function doDefault()
	{
		$this->redirect('action=list&view=awaiting_payment');
	}

	public function doList()
	{
		$select = M('Order')->alias('o')
			->leftJoin(M('User')->getTableName().' AS b', 'o.buyer_id = b.id')
			->leftJoin(M('User')->getTableName().' AS s', 'o.seller_id = s.id')
			->leftJoin(M('Payment')->getTableName().' AS p', 'o.payment_id = p.id')
			->leftJoin(M('Shipping')->getTableName().' AS d', 'o.shipping_id = d.id')
			->columns('o.*, b.username AS buyer_account, s.username AS seller_account, p.name AS payment_name, d.name AS shipping_name')
			->order('id DESC')
			->paginator(10, $this->_request->page);

		switch ($this->_request->view) {
			case 'awaiting_payment': $select->where('o.status = 1'); break;
			case 'shiped': $select->where('o.status = 2'); break;
			case 'pending_receipt': $select->where('o.status = 3'); break;
			case 'completed': $select->where('o.status = 4'); break;
			case 'closed': $select->where('o.status = 0'); break;
		}
		
		if ($this->_request->q) {
			$select->where('o.code = ?', $this->_request->q);
		}
		if ($this->_request->consignee) {
			$select->where('o.consignee LIKE ?', '%'.$this->_request->consignee.'%');
		}
		if ($this->_request->buyer) {
			$select->where('b.username LIKE ?', '%'.$this->_request->buyer.'%');
		}
		if ($this->_request->start_time) {
			$select->where('o.create_time >= ?', strtotime($this->_request->start_time));
		}
		if ($this->_request->end_time) {
			$select->where('o.create_time <= ?', strtotime($this->_request->end_time) + (3600 * 24));
		}

		$view = $this->_initView();
		$view->datalist = $select->fetchRows();
		$view->render('order/list.php');
	}

	public function doDetail()
	{
		$order = M('Order')->getById((int)$this->_request->id);
		if (!$order->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}

		$view = $this->_initView();
		$view->data = $order;
		$view->render('order/detail.php');
	}

	public function doCancel()
	{
		$order = M('Order')->getById((int)$this->_request->id);
		if (!$order->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}
		if ($order['status'] == 2 || $order['status'] == 3) {
			throw new App_Exception('不能取消正在进行中的订单');
		}

		$order->cancel();
		$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');

	}

	public function doPrint()
	{
		$order = M('Order')->getById((int)$this->_request->id);
		if (!$order->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}

		$view = $this->_initView();
		$view->data = $order;
		$view->render('order/print/'.$this->_request->v.'.php');
	}

	public function doAdjustment()
	{
		if ($this->_request->isPost()) {
			$amount = ($this->_request->getPost('amount') * $this->_request->getPost('algorithm'));
			$order = M('Order')->getById((int)$this->_request->getPost('id'));
			$order->adjustment_amount = $amount;
			$order->total_pay_amount = $order->total_amount+$order->total_freight-$order->total_save+$amount;
			$order->logs .= "\n".M('Admin')->getCurUser()->username." : {调整金额".$amount."} -- ".date('y/m/d H:i:s');
			$order->save();
			$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
		}
	}

	public function doConsignee()
	{
		$order = M('Order')->getById((int)$this->_request->id);

		if ($this->_request->isPost()) {
			M('Order')->update($_POST, 'id = '.(int)$this->_request->id);
			$order->logs .= "\n".M('Admin')->getCurUser()->username." : {修改收货地址} -- ".date('y/m/d H:i:s');
			$order->save();
			$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
		}
		$view = $this->_initView();
		$view->data = $order;
		$view->render('order/consignee.php');
	}

	public function doPay()
	{
		if ($this->_request->success) {
			return $this->_notice(array(
				'title' => '收款成功！',
				'links' => array(
					array('查看当前订单', 'action=detail&id='.(int)$this->_request->id),
					array('查看待发货的订单', 'action=list&view=shiped')
				)
			), 'success');
		}
		$order = M('Order')->getById((int)$this->_request->id);
		if ($order['status'] != 1) { throw new App_Exception('禁止操作'); }
		if ($order['expiry_time'] < time()) {
			//throw new App_Exception('交易超时');
		}
		if ($order->exists()) {
			$order->pay();
			$order->logs .= "\n".M('Admin')->getCurUser()->username." : {将订单改为已付款} -- ".date('y/m/d H:i:s');
			$order->save();
			$this->redirect('&success=1&pst=order');
		}
	}

	public function doDelivery()
	{
		if ($this->_request->success) {
			return $this->_notice(array(
				'title' => '发货成功！',
				'links' => array(
					array('查看当前订单', 'action=detail&id='.(int)$this->_request->id),
					array('查看待发货的订单', 'action=list&view=shiped')
				)
			), 'success');
		}
		$order = M('Order')->getById((int)$this->_request->id);
		if ($order['status'] != 2) {
			throw new App_Exception('禁止操作');
		}
		if ($order['expiry_time'] < time()) {
			//throw new App_Exception('交易超时');
		}
		if ($this->_request->isPost()) {
			$order->logs .= "\n".M('Admin')->getCurUser()->username." : {发货} -- ".date('y/m/d H:i:s');
			$order->delivery($_POST['code'], $_POST['remark']);
			$this->redirect('&success=1&pst=order');
		}

		$view = $this->_initView();
		$view->data = $order;
		$view->shipping = M('Shipping')->select()->fetchRows();
		$view->render('order/delivery.php');
	}

	public function doReset()
	{
		switch ($this->_request->status) {
			case 2:
				M('Order')->update(array(
					'expiry_time' => time() + (int)M('Setting')->timeout_delivery,
					'logs' => $order['logs'] . date(DATETIME_FORMAT)." - 被管理员".M('Admin')->getCurUser()->username."重置 \r\n"
				), 'id = '.(int)$this->_request->id);
				break;
		}
		$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
	}

	public function doDelete()
	{
		$order = M('Order')->getById((int)$this->_request->id);
		if (!$order->exists()) {
			throw new Suco_Controller_Dispatcher_Exception('Not found.');
		}
		if ($order['status'] == 2 || $order['status'] == 3) {
			throw new App_Exception('不能删除正在进行中的订单');
		}

		M('Order')->deleteById((int)$this->_request->id);
		$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list');
	}

	public function doBatchDelete()
	{
		foreach ((array)$this->_request->getPost('ids') as $id) {
			$order = M('Order')->getById((int)$id);
			if ($order['status'] == 2 || $order['status'] == 3) {
				echo '<script>alert(\'不能删除正在进行中的订单['.$order['code'].']\')</script>';
				continue;
			}

			M('Order')->deleteById((int)$id);
		}
		$this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=list', 'js');
	}

	public function doBatchProcess()
	{
		M('Order')->process();
	}
}
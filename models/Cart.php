<?php

class Cart
{
	protected $_items = array();
	protected $_status = array();
	protected $_observer = array(
		'Cart_Observer_Goods',
		#'Cart_Observer_Package',
		'Cart_Observer_Shipping',
		'Cart_Observer_Coupon',
		'Cart_Observer_Activity',
	); //观察者

	/**
	 * 构造
	 */
	public function __construct()
	{
		$data = json_decode(Suco_Cookie::get(__CLASS__), true);
		$this->_items = (array)$data['items'];
		//$this->_status = (array)$data['status'];
		
		//读取会员购物车
		// $uid = M('User')->getCurUser()->id;
		// if ($uid && !$this->_items) {
		// 	$items = M('User_Cart')->select()
		// 		->where('user_id = ?', $uid)
		// 		->fetchRows();
		// 	foreach ($items as $item) {
		// 		$k = $item['goods_id'].'.'.$item['sku_id'];
		// 		$this->_items[$k] = array(
		// 			'id'=> $item['goods_id'], 
		// 			'qty'=> $item['qty'], 
		// 			'skuId'=> $item['sku_id'],
		// 			'priceType' => $item['price_type']
		// 		);
		// 	}
		// }
	}

	/**
	 * 添加观察者
	 */
	public function addObserver($class)
	{
		$this->_observer[$class] = $class;
		return $this;
	}

	/**
	 * 删除观察者
	 */
	public function delObserver($class)
	{
		unset($this->_observer[$class]);
		return $this;
	}

	/**
	 * 设置多个观察者
	 */
	public function setObservers($observer)
	{
		$this->_observer = $observer;
		return $this;
	}

	/**
	 * 返回多个观察者
	 */
	public function getObservers()
	{
		return $this->_observer;
	}

	/**
	 * 添加商品
	 * @param int $id 商品ID
	 * @param int $qty 数量
	 * @param mixed $opts 规格选项
	 * @param bool $reset 是否重置
	 * @return string
	 */
	public function addItem($id, $skuId = 0, $qty = 1, $priceType = 0, $checkout = 0, $reset = 0)
	{
		if (!$id) return;
		$code = $id.'.'.$skuId;
		if (!isset($this->_items[$code]) || $reset) {
			$this->_items[$code] = array('id'=>$id, 'qty'=>$qty, 'skuId'=>$skuId, 'priceType'=>$priceType, 'checkout'=>$checkout);
		} else { //追加商品
			$this->_items[$code]['qty'] += $qty;
		}
		$this->save();
		return $code;
	}

	/**
	 * 移除商品
	 * @param string $code 商品标签
	 */
	public function delItem($code)
	{
		unset($this->_items[$code]);
		$this->save();
	}

	/**
	 * 设置购物车中的商品
	 * @return array
	 */
	public function setItems($items)
	{
		$this->_items = $items;
		return $this;
	}

	/**
	 * 返回购物车中的商品
	 * @return array
	 */
	public function getItems()
	{
		return $this->_items;
	}

	/**
	 * 返回商品种类数
	 * @return int
	 */
	public function getTotal()
	{
		return $this->_items ? count($this->_items) : 0;
	}

	/**
	 * 返回商品件数
	 * @return int
	 */
	public function getTotalQty()
	{
		$qty = 0;
		foreach($this->_items as $item) {
			$qty += $item['qty'];
		}
		return $qty;
	}

	/**
	 * 返回购物车中商品金额
	 * @return int
	 */
	public function getTotalAmount()
	{
		$amount = 0;
		foreach($this->_items as $item) {
			$amount += $item['subtotal'];
		}
		return $amount;
	}

	/**
	 * 设置购物车状态
	 */
	public function setStatus($k, $v)
	{
		$this->_status[$k] = $v;
		return $this;
	}

	/**
	 * 返回购物车状态
	 */
	public function getStatus($k)
	{
		return $this->_status[$k];
	}

	/**
	 * 设置购物车所有状态
	 */
	public function setAllStatus($status)
	{
		$this->_status = $status;
	}

	/**
	 * 返回购物车所有状态
	 */
	public function getAllStatus()
	{
		return $this->_status;
	}

	/**
	 * 销毁购物车
	 */
	public function destroy()
	{
		$this->_items = null;
		$this->_status = null;
		$this->save();
	}

	/**
	 * 保存购物车
	 */
	public function save()
	{
		Suco_Cookie::set(__CLASS__, json_encode(array(
			'status' => $this->_status,
			'items' => $this->_items
		)), 3600*24*30);

		//保存会员购物车
		// $uid = M('User')->getCurUser()->id;
		// if ($uid) {
		// 	M('User_Cart')->delete('user_id = ?', $uid);
		// 	foreach($this->_items as $k => $item) {
		// 		M('User_Cart')->insert(array(
		// 			'user_id' => $uid,
		// 			'goods_id' => $item['id'],
		// 			'sku_id' => $item['skuId'],
		// 			'qty' => $item['qty'],
		// 			'checkout' => $item['checkout']
		// 		));
		// 	}
		// }
	}

	/**
	 * 开始结算
	 * @param mixed $codes 需结算的商品标签
	 * @return array
	 */
	public function checking($codes = 'all')
	{
		//只保留结算项目
		if ($codes != 'all') {
			$codes = is_array($codes) ? $codes : explode(',', $codes);

			foreach ($this->_items as $code => $item) {
				if (!in_array($code, $codes)) {
					unset($this->_items[$code]);
				}
			}
		}
		
		//将信息交给观察者处理
		foreach ((array)$this->_observer as $class) {
			M($class)->observer($this);
		}

		return $this;
	}
}
<?php

class Admincp_ShippingController extends Admincp_Controller_Action
{
	public function init()
	{
		$this->_auth();
	}

	public function doSetting()
	{
		$view = $this->_initView();
		$view->data = M('Shipping')->getById((int)$this->_request->id);
		$view->datalist = M('Shipping_Freight')->select()
			->where('shipping_id = ?', (int)$this->_request->id)
			->fetchRows();
		$view->render('shipping/setting.php');
	}
}
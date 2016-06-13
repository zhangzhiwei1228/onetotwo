<?php

class Usercp_CreditController extends Usercp_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->user = $this->_auth();
	}

	public function doDefault()
	{
		$view = $this->_initView();
		$view->logs = M('User_Credit')->select()
			->where('user_id = ?', $this->user['id'])
			->order('id DESC')
			->paginator(20, $this->_request->page)
			->fetchRows();
		$view->render('views/payrecord.php');
	}
}
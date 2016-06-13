<?php

class Agent_IndexController extends Agent_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->user = $this->_auth();
	}
	
	public function doDefault()
	{
		switch ($this->user['role']) {
			case 'staff':
				$view = $this->_initView();
				$view->parent = M('User')->getById((int)$this->user['parent_id']);
				$view->bonus = $this->user->getBonus();
				$view->render('views/proxyworker.php');
				break;
			case 'resale':
				if ($this->user['resale_grade'] == 4) {
					$view = $this->_initView();
					$view->bonus = $this->user->getStaffBonus();
					$view->render('views/proxyfour.php');
				} else {
					$view = $this->_initView();
					$view->bonus = $this->user->getBonus();
					$view->render('views/onestar.php');
				}
				break;
			case 'seller':
			case 'agent':
				$view = $this->_initView();
				$view->bonus = $this->user->getStaffBonus();
				$view->render('views/merchants.php');
				break;
		}
	}

	public function doRecharge()
	{
		$view = $this->_initView();
		$view->render('views/jifensteps.php');
	}

	public function doStaff()
	{
		$view = $this->_initView();
		$view->render('views/merchants/merchants_staffw.php');
	}
	public function doShopList() {
		$view = $this->_initView();
		$view->render('views/shoplist.php');
	}
}
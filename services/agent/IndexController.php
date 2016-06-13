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
		if($this->user['role'] != 'resale') $this->redirect(isset($this->_request->ref) ? base64_decode($this->_request->ref) : 'action=default');
		$start_time = strtotime(date('Y-m-01 00:00:00', time()));
		$end_time = strtotime(date('Y-m-d 23:59:59',strtotime(date('Y-m-01 23:59:59', time()).' +1 month -1 day')));
		$merchants = M('User')->alias('u')
			->leftJoin(M('User_Credit')->getTableName().' AS ct', 'ct.user_id = u.id')
			->group('ct.user_id')
			->columns('u.*, SUM(ct.credit) AS remain, SUM(IF(ct.credit > 0, ct.credit, 0)) AS recharge, SUM(IF(ct.credit < 0, ct.credit, 0)) AS consume, ct.id as ct_id')
			->where('u.parent_id = '.$this->user['id'].' and ct.create_time <= '.$end_time.' and ct.create_time >= '.$start_time)
			->order('u.id DESC');
		$view = $this->_initView();
		$view->merchants = $merchants->fetchRows();;
		$view->render('views/shoplist.php');
	}
}
<?php

class Usercp_VipController extends Usercp_Controller_Action
{
	public function init()
	{
		parent::init();
		//$this->user = $this->_auth();
	}

	public function doDefault()
	{
		$view = $this->_initView();
		$view->render('views/member.php');
	}

	public function doLevel()
	{
		$view = $this->_initView();
		$view->render('views/member_upgrade.php');
	}

	public function doActive()
	{
		$this->user = $this->_auth();
		if ($this->_request->isPost()) {
			$cfg = new Suco_Config_Php();
			$setting = $cfg->load(CONF_DIR.'vip.conf.php');
			$_POST['amount'] = $setting[$_POST['type']];

			$view = $this->_initView();
			$view->payments = M('Payment')->select()
				->where('is_enabled = 1')
				->order('rank ASC, id ASC')
				->fetchRows();
			$view->render('views/payway.php');
			die;
		}
	}

	public function doApply()
	{
		if ($this->_request->isPost()) {
			$_POST['grade'] = $this->_request->vip;
			M('Resale_Apply')->insert($_POST);

			$url = H('Url', 'module=usercp');
			echo '<script>alert(\'已提交申请\'); window.location = \''.$url.'\'</script>';
			return;
		}

		$view = $this->_initView();
		$view->render('views/member_act.php');
	}
}
<!DOCTYPE html>
<head>
<?php include_once VIEWS.'inc/head.php'; ?>
</head>

<body class="bgcolor">
    <?php //include_once VIEWS.'inc/header.php'; ?>
    <div class="z-jifenheader bgcolor<">
		<div class="header-merchants">
			<div class="main"><a href="#"><img src="<?php echo static_file('m/img/icon18.png'); ?> "></a>商家管理员收益</div>
		</div>
	</div>
	<div class="admin-box bgwhite">
		<div class="merc-admin w90">
			<div class="pic fl"><img src="<?php echo static_file('m/img/pic17.png'); ?> "></div>
			<div class="intro fl">
				<p class="admin-p">创业商家管理员</p>
				<p class="admin-name">周星星</p>
				<p class="login-info"><a href="#">退出</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#">修改密码</a></p>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="staff-jifen bgwhite"><p class="w90">我员工发展的一级会员消费积分币：<span class="fr"><em>10000</em>&nbsp;币</span></p></div>
	<div class="staff-jifen bgwhite"><p class="w90">我员工发展的一级会员消费积分币：<span class="fr"><em>10000</em>&nbsp;币</span></p></div>
	<div class="month-income bgwhite">
		<p class="income01 w90">我的本月收益</p>
		<p class="income01 w90"><span>10000</span>&nbsp;元</p>
	</div>
	<div class="member-rank bgwhite">
		<p class="w90">一级会员：我直接邀请注册的会员</p>
		<p class="w90">二级会员：我的一级会员邀请注册的会员</p>
	</div>
    <?php include_once VIEWS.'inc/footer_merchants.php'; ?>
<?php
	echo static_file('web/js/main.js');
?>
</body>
</html>



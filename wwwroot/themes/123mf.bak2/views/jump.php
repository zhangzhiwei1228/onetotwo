<!DOCTYPE html>
<html>
<head>
<?php include_once VIEWS.'inc/head.php'; ?>
</head>
<body>
	<div class="jump">
		<div class="n-personal-center-tit">
			<a href="<?=$this->url('/default')?>"><img width="11" height="21" src="<?php echo static_file('mobile/img/img-22.png'); ?> " alt=""></a>
			充值跳转
		</div>
		<div class="jump-box">
			<input onclick="window.open('<?=$this->url('usercp/money/recharge?=t=credit_coin')?>')" value="充值积分币" type="submit">
			<input onclick="window.open('')" value="充值抵用卷" type="submit">
			<input onclick="window.open('<?=$this->url('usercp/money/recharge?=t=credit_happy')?>')" value="充值快乐积分" type="submit">
			<input onclick="window.open('<?=$this->url('usercp/money/recharge?=t=credit')?>')" value="充值免费积分" type="submit">
		</div>
	</div>
</body>
</html>
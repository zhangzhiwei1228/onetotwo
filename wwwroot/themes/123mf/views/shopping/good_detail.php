<!DOCTYPE html>
<head>
<?php include_once VIEWS.'inc/head.php'; ?>
</head>

<body class="bgcolor">

    <?php include_once VIEWS.'inc/header_shop02.php'; ?>
    <div class="swiper-container bgwhite">
	    <div class="swiper-wrapper">
	        <?php $images = json_decode($this->data['ref_img'],1); $preview = $images[0]; ?>
	    	<?php foreach((array)$images as $row) { ?>
	        <div class="swiper-slide"><img src="<?=$this->baseUrl($row['src'])?> " width="100%" /></div>
			<?php } ?>
	    </div>
	    <!-- 如果需要分页器 -->
	    <div class="swiper-pagination"></div>  
	</div>
	<p class="product-name bgwhite"><?=$this->data['title']?></p>
	<form method="post" class="bgwhite buyform" action="<?=$this->url('cart/add')?>">
		<input type="hidden" name="goods_id" value="<?=$this->data['id']?>">
		<input type="hidden" name="sku_id" value="<?=$this->sku['id']?>">
		<input type="hidden" name="buynow" value="">
		<div class="boline">
			<div class="discount-box w90">
				<span class="discount-price fl">促销价:</span>
				<label class="fl">
					<?php if ($this->data->skus[0]['point1']) { ?>
					<p class="select"><input type="radio" name="price_type" value="1" checked="" />&nbsp;&nbsp;<?=$this->data->skus[0]['point1']?> 快乐积分</p>
					<?php } ?>
					<?php if ($this->data->skus[0]['point2']) { ?>
					<p class="select"><input type="radio" name="price_type" value="2" />&nbsp;&nbsp;<?=$this->data->skus[0]['point2']?> 免费积分</p>
					<?php } ?>
					<?php if ($this->data->skus[0]['exts']['ext1']['cash']) { ?>
					<p class="select"><input type="radio" name="price_type" value="3" />&nbsp;&nbsp;<?=$this->data->skus[0]['exts']['ext1']['cash']?> 元 + <?=$this->data->skus[0]['exts']['ext1']['point']?> 免费积分</p>
					<?php } ?>
					<?php if ($this->data->skus[0]['exts']['ext2']['cash']) { ?>
					<p class="select"><input type="radio" name="price_type" value="4" />&nbsp;&nbsp;<?=$this->data->skus[0]['exts']['ext2']['cash']?> 元 + <?=$this->data->skus[0]['exts']['ext2']['point']?> 积分币</p>
					<?php } ?>
				</label>
				<p class="original fl">原  价:&nbsp;&nbsp;<span>¥<?=$this->data->skus[0]['market_price']?> 元</span></p><p class="selled fr">已售  <?=$this->data['sales_num']?>件</p>
				<div class="clear"></div>
			</div>
		</div>
		<!-- <div class="boline">
			<div class="send-addess w90">
				<select class="fl">
					<option>配送至浙江省</option>
					<option>xxxx</option>
					</option>
				</select>
				<p class="fl postage">邮费  10元</p>
				<p class="fr">A类综合打包</p>
				<div class="clear"></div>
			</div>
		</div> -->
		<div class="boline">
			<div class="buy-muns w90">
				<span class="title fl">购买数量：</span>
				<div class="click-nums fl">
		            <a href="javascript:void(0);" class="add">+</a>
		            <input type="text" name="quantity" value="1">
		            <a href="javascript:void(0);" class="jian">-</a>
				</div>
				<span class="fl">&nbsp;&nbsp;&nbsp;&nbsp;库存 <?=$this->data['quantity']?>件</span>
				<div class="clear"></div>
			</div>
		</div>
		<?php
		$opts = $this->data->getSkuOpts();
		foreach((array)$opts as $row) { ?>
		<div class="boline">
			<div class="good-style w90">
				<p class="style-name"><?=$row['name']?></p>
				<ul>
					<?php
					foreach($row['values'] as $k => $v) { ?>
					<li><a href="javascript:;" data-param="<?=$row['name']?>:<?=$v?>" onclick="$.buychoose(this)"></a><?=$v?></li>
					<?php } ?>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<?php } ?>
	</form>
	<div class="product-details-box bgwhite">
		<p class="product-headline"><em class="fl"></em>&nbsp;&nbsp;&nbsp;&nbsp;<span class="cn">产品详情</span>&nbsp;&nbsp;<span class="en"></span>Product&nbsp; details</span></p>.
		<p class="pic"><?=$this->data['description']?></p>
	</div>
	<div class="end163">
		请选择分类
	</div>
    <!--<?php //include_once VIEWS.'inc/footer_shopping.php'; ?>-->
    <?php //include_once VIEWS.'inc/footer01.php'; ?>
    <?php include_once VIEWS.'inc/footer_buying.php'; ?>
<?php
	echo static_file('m/js/main.js');
	
?>


<script type="text/javascript">

	function limit(){
		var avId = new Array();
		$(".bgwhite ul li").each(function(i,el){
			var $el = $(el);
			if ($el.hasClass('goodstylecurr')) {
				avId.push( $el.data("id"));
			};
		})
		var str_avId=avId.join();
		// alert(str_avId)
	}


	$(function(){
		$(".footer-buying-box li").eq(0).addClass('cur');
	})
	// $('.btn-addcart').on('click', function(){
	// 	$('.buyform').submit();
	// });

	// $('.btn-buynow').on('click', function(){
	// 	$('.buyform').submit();
	// });
</script>

<script type="text/javascript">
	var mySwiper = new Swiper ('.swiper-container', {
    loop: true,
    
    // 如果需要分页器
    pagination: '.swiper-pagination',
    autoplay: 2000
    
  	})   
	$('.discount-box label .select').click(function(){
	 	$(this).addClass('selectcurr').siblings('.select').removeClass('selectcurr');
	});
  
  	$('.good-style ul li').click(function(){
  		$(this).addClass('goodstylecurr').siblings('li').removeClass('goodstylecurr');
  		limit();
  	});

  	$('input:radio[name="price_type"]').click(function(){
  	$(".bgwhite .boline").eq(0).css("border","0px none");
  	$(".bgwhite .boline").eq(0).css("border-bottom","1px solid #f1f0f0");
  	})

	$(".click-buy").click(function(){
		var val=$('input:radio[name="price_type"]:checked').val();
		var flag = 1;
		if(val==null){
			$("html,body").stop().animate({scrollTop:$(".bgwhite .boline").eq(0).offset().top - 90});
			$(".bgwhite .boline").eq(0).css("border","2px solid #fc0000");
			 flag = 0;
		} else {
			$("html,body").stop().animate({scrollTop:$(".style-name").eq(0).offset().top - 90});
			 flag = 1;
		}

  	$(".good-style ul").each(function(){
  		if ($(this).children('li').hasClass('goodstylecurr')) {
  		}else{
  			
  			$(this).parents(".boline").css("border","2px solid #fc0000");
  			setTimeout(function(){
  				$(this).css("border","0px none");
  			},3000);
  			flag = 0;
  			$(".end163").show();
  			setTimeout(function(){
  				$(".end163").hide();
  			},3000);
  			flag = 0;
  			
  		}
  	})

  	if (flag) {
  		$('[name=buynow]').val(1);
		<?php
			$_SESSION['pay_confirm_login'] = true;
			$_SESSION['pay_confirm_login_url'] = true;
		?>
		$('.buyform').submit();
	}

	
 	//$('.buyform').submit();
  })

$(function(){

  	// $('.buy-muns .click-nums .add').click(function(){
   //      var $this = $(this),
   //          $input = $this.parent().find('input'),

   //          num = parseInt($input.val());
   //      $input.val(num+1);

   //    })

   //  $('.buy-muns .click-nums .jian').click(function(){
   //      var $this = $(this),
   //          $input = $this.parent().find('input'),
   //          num = parseInt($input.val());
   //      if(num > 1){
   //          $input.val(num-1);

   //      }else{
   //          $input.val(1);

   //      }
   //  })

   //  $('.click-nums input').keyup(function() {
   //      var n = parseInt($(this).parent().find("input").val());
   //      if(n > 1){
   //          $(this).parent().find("input").val(n);

   //      }else{
   //          $(this).parent().find("input").val(1);

   //      }
   //  });
});

$(".btn-addcart").click(function(){

	

	var flag = 1;
  	$(".good-style ul").each(function(){
  		if ($(this).children('li').hasClass('goodstylecurr')) {
  		}else{
  			$(this).parents(".boline").css("border","2px solid #fc0000");
  			$("html,body").stop().animate({scrollTop:$(".style-name").eq(0).offset().top - 80});
  			$(".end163").show();
  			setTimeout(function(){
  				$(".end163").hide();
  			},3000);
  			flag = 0;
  		}
  		var val=$('input:radio[name="price_type"]:checked').val();
		if(val==null){
			$("html,body").stop().animate({scrollTop:$(".bgwhite .boline").eq(0).offset().top - 90});
			$(".bgwhite .boline").eq(0).css("border","2px solid #fc0000");
			flag = 0;
		} else {
			$("html,body").stop().animate({scrollTop:$(".style-name").eq(0).offset().top - 90});
			flag = 1;
		}
  	})

  	if (flag) {
  		$('[name=buynow]').val(0);
  		$('.buyform').submit();
	}
  });

	$(".good-style ul li").click(function(){
		$(this).parents(".boline").css("border","0px none")
	})

</script>
</body>
</html>



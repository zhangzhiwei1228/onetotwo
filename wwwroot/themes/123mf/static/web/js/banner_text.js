$(function(){
	var listN = $(".banner li").size();
	for(i=0;i<listN;i++){
		 $(".number").append('<span></span>');
}
	// if (listN > 1){		
	// 	for(i=0;i<listN;i++){
	// 	 $(".number").append('<span></span>');		
	// 	}
	// }

	$(".banner ul li").eq(0).css("display","block").fadeIn(600);
	$(".banner ul li:eq(0) .con").stop().animate({"margin-left":-480}, 800);
	// $(".banner li:eq(0) p.en").stop().animate({"margin-left":-480}, 1500);

	$(".banner .number span").eq(0).addClass("on")
	var sw = 1;
	$(".banner .number span").mouseover(function(){
		sw = $(".number span").index(this);
		myShow(sw);
	});
	function myShow(i){
		$(".banner ul li").eq(i).stop(true,true).fadeIn(600).siblings("li").fadeOut(600);
		$(".banner ul li:eq("+i+") .con").stop().animate({"margin-left":-480}, 800);
		// $(".banner li:eq("+i+") p.en").stop().animate({"margin-left":-480}, 1500);

		$(".banner ul li").each(function(){
			if (i != $(this).index()) {
				$(this).find(".con").stop().css({"margin-left":0});
				// $(this).find("p.en").stop().css({"margin-left":0});
			};
		})
//////////////////////////////////////////////////////////////////////
		$(".banner .number span").eq(i).addClass("on").siblings("span").removeClass("on");
	}
	//滑入停止动画，滑出开始动画
	$(".banner").hover(function(){
		if(myTime){
		   clearInterval(myTime);
		}
	},function(){
		myTime = setInterval(function(){
		  myShow(sw)
		  sw++;
		  if(sw==listN){sw=0;}
		} , 5000);
	});
	//自动开始
	var myTime = setInterval(function(){
	   myShow(sw)
	   sw++;
	   if(sw==listN){sw=0;}
	} , 3500);
})

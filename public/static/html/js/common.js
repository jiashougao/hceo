//导航下拉
function pcNavDown(boxid,_name,_hover,_down){
	var _box = $(boxid);
	var _arr = _box.find(_name);
	var _index = _box.find("."+_hover).index();
	_arr.hover(function(){
		$(this).addClass(_hover).siblings().removeClass(_hover);
		if($(this).find(_down).is(":animated")){
           $(this).find(_down).stop(true,true);
		   }
		$(this).find(_down).slideDown(300);
		},function(){
			if($(this).find(_down).is(":animated")){
               $(this).find(_down).stop(true,true);
		       }
		    $(this).find(_down).slideUp(300);
			});
	_box.mouseleave(function(){
		_arr.eq(_index).addClass(_hover).siblings().removeClass(_hover);
		});
}


//页面计算问题
function setRootSize() {
	var deviceWidth = document.documentElement.clientWidth; 
	if(deviceWidth>750){deviceWidth = 750;}  
	document.documentElement.style.fontSize = deviceWidth / 7.5 + 'px';
}
setRootSize();
window.addEventListener('resize', function () {
    setRootSize();
}, false);
$(document).ready(function(){
	setRootSize();
});

// 头部搜索出现
function heaSearch(){
	$(".hea_sub").hover(function(){
		$(".hea_search").addClass("heasea_show");
	})
	$(".hea_search").mouseleave(function(){
		$(this).removeClass("heasea_show")
	})	
}

//导航
function navMenu(){
	var open = $(".hea_menuclick");
	var con = $(".hea_menuhide");
	open.click(function(){
		if($(this).hasClass("sel")){
			con.fadeOut(300);			
			$(this).removeClass("sel");
		}else{
			$(this).addClass("sel");
			con.fadeIn(300)
		}
	})
}

function seaDown(){
	var hide = $(".vl_key_hide");
	var hidea = hide.find("a");
	var keyopen = $(".vl_p");
	var _con = $("body");
	keyopen.click(function(){
		if(hide.is(':hidden')){
			hide.slideDown(300);
			$(this).addClass("sel")
		}else{
			hide.slideUp(300);
			$(this).removeClass("sel")
		}
	})
	hidea.click(function(){
		keyopen.text($(this).text());
	})

    function stopPropagation(e) {
        if (e.stopPropagation) 
            e.stopPropagation();
        else 
            e.cancelBubble = true;
    }

    $(document).bind('click',function(){
        hide.slideUp(300);
		keyopen.removeClass("sel")
    });

    keyopen.bind('click',function(e){
        stopPropagation(e);
    });	
}

$(document).ready(function(){
	//首页banner
	  var mySwiper = new Swiper('.banner',{
		effect : 'fade',
		fadeEffect: {
		  crossFade: true,
		},			  
	    loop: true,
		speed:600,
		grabCursor : true,
		parallax:true,
		autoplay:{
		  delay: 3000,
		//loop无效  stopOnLastSlide: true,
		},	
		pagination: {
		  el:'.banner_num',
		  clickable :true,
		}
	}); 
	//移动端footer轮播
	if (screen.width<=750){
	    var swiper2 = new Swiper('.foo_coop', {
	      slidesPerView: 3,
	      slidesPerColumn: 3,
	      spaceBetween:10,
	      pagination: {
	        el: '.foocop_num',
	        clickable: true,
	      },
	    });  		
	}  	
	
	//商品左侧详情图片轮播
	var proSwiper = new Swiper('.prodea_img',{
		effect : 'fade',
		fadeEffect: {
		  crossFade: true,
		},			  
		loop: true,
		speed:600,
		grabCursor : true,
		parallax:true,
		autoplay:{
		  delay: 3000,					
		},
		navigation: {
		  nextEl: '.prodea_next',
		  prevEl: '.prodea_prev',
		}				
	});  	
	
	//商品详情右侧信息轮播
	var proSwiper = new Swiper('.prodea_rinfo',{
		effect : 'fade',
		fadeEffect: {
		  crossFade: true,
		},			  
		loop: true,
		speed:600,
		grabCursor : true,
		parallax:true,
		autoplay:{
		  delay: 3000,					
		},
		navigation: {
		  nextEl: '.proinfo_next',
		  prevEl: '.proinfo_prev',
		}				
	}); 	
})

//活动视频页面文字切换
function actTab(){
	var show = $(".actvdo_show");
	var tab = $(".actvdo_tab a");
	var index = 0;
	show.eq(index).show();
	tab.eq(index).addClass("sel")
	
	tab.click(function(){
		index = $(this).index();
		$(this).addClass("sel").siblings().removeClass("sel")
		if(show.eq(index).is(":animated")){
           show.eq(index).stop(true,true);
		}		
		show.eq(index).fadeIn(500).siblings().hide();
	})
}
 

//买家个人中心切换
function personalTab(tab,show,num){
	var a = $(tab);
	var show = $(show);
	var index = num;
	show.eq(index).show();
	a.eq(index).addClass("sel");
	
	a.click(function(){
		$(this).addClass("sel").siblings().removeClass("sel")
		index = $(this).index();
		if(show.eq(index).is(":animated")){
           show.eq(index).stop(true,true);
		}		
		show.eq(index).fadeIn(500).siblings().hide()
	})
}

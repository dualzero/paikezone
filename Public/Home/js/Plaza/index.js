$(function(){

	//header好友动态默认
	$('#nav ul').children().eq(2).find('a').css('color','#E54839');

	// //按钮--关注
	// $('#center .center_bottom .care').button();

	//点击--关注
	$('#center .center_bottom .ui-button-text-only .ui-button-text').click(function(){
		$(this).text('已关注');
		$(this).css('color','#E86D44');
	});



	/*瀑布流开始*/
	var container = $('.waterfull ul');
	var loading=$('#imloading');
	// 初始化loading状态
	loading.data("on",true);

	/*设置瀑布流最大布局宽度，最大为960*/
	$('.waterfull').width('960');
	//设置瀑布流容器属性
	container.imagesLoaded(function(){
		container.masonry({
			// columnWidth: 285,
			itemSelector : '.item',
			isFitWidth: true,//是否根据浏览器窗口大小自动适应默认false
			isAnimated: false,//是否采用jquery动画进行重拍版
			isRTL:false,//设置布局的排列方式，即：定位砖块时，是从左向右排列还是从右向左排列。默认值为false，即从左向右
			isResizable: true,//是否自动布局默认true
			animationOptions: {
				duration: 800,
			//	easing: 'easeInOutBack',//如果你引用了jQeasing这里就可以添加对应的动态动画效果，如果没引用删除这行，默认是匀速变化
				queue: false//是否队列，从一点填充瀑布流
			}
		});
	});

	//屏幕滚动事件
	$(window).scroll(function(){
		if(!loading.data("on")) return;
		// 计算所有瀑布流块中距离顶部最大，进而在滚动条滚动时，来进行ajax请求，方法很多这里只列举最简单一种，最易理解一种
		var itemNum=$('#waterfull').find('.item').length;
		var itemArr=[];
		itemArr[0]=$('#waterfull').find('.item').eq(itemNum-1).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
		itemArr[1]=$('#waterfull').find('.item').eq(itemNum-2).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
		itemArr[2]=$('#waterfull').find('.item').eq(itemNum-3).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
		var maxTop=Math.max.apply(null,itemArr);
		if(maxTop<$(window).height()+$(document).scrollTop()){
			//加载更多数据
			loading.data("on",false).fadeIn(800);
			//向服务器请求数据
			var url = Think.ROOT + '/Home/Plaza/get_pics';
			var info = {
				'start' : itemNum,
				'type' : container.attr('value')
			};
			console.log(info);
			$.post(url,info,success,'json');
			function success(data){
				console.log(data);
				if(data.status == 0){
					loading.text('没有更多了！');
				}else{
					var list = data.info;
					console.log(list);
					var html = '';
					for(var i in list){
						html += '<li class="item">'
							  + '	<a href=""><img src="'+list[i].path+'"></a>'
							  + '	<div class="info">'
							  + '		<div class="left"><img src="'+list[i].avatar+'"></div>'
							  + '		<div class="right">'
							  + '		<p><span class="color user">'+list[i].nickname+'}</span> 上传了照片到 <span class="color album">'+list[i].name+'</span></p>'
							  + '		<p style="font-size:13px;">'+list[i].create_time+'</p>'
							  + '		</div>'
							  + '	</div>'
							  + '</li>';
						//加载图片
					}
					var time = setTimeout(function(){
						//图片预加载
						$(html).find('img').each(function(){
							loadImage($(this).attr('src'));
						});
						//添加在本来的图片后面
						var $html = $(html).css({opacity:'0'}).appendTo(container);
						//如果已经加载完图片，则显示
						$html.imagesLoaded(function(){
							$html.animate({ opacity: 1},1000);
							//调整新加入元素的位置--调整为瀑布流模式
							container.masonry( 'appended', $html,true);
							loading.data("on",true).fadeOut();//去除等待
							clearTimeout(time);
						});
					},800);
				}
			}
			// (function(sqlJson){
			// 	/*这里会根据后台返回的数据来判断是否你进行分页或者数据加载完毕这里假设大于30就不在加载数据*/
			// 	if(itemNum>100){
					
			// 	}else{
			// 		var html="";
			// 		for(var i in sqlJson){
			// 			html+="<li class='item'><a href='#'><img src='"+sqlJson[i].src+"'></a><div class='info'><div class='left'><img src='image/user.jpg'></div><div class='right'><p><span class='color user'>×××</span>上传了照片到<span class='color album'>相册名称</span></p><p style='font-size:13px;'>2015-8-22 22:54</p></div></div></li>";		
			// 		}
			// 		/*模拟ajax请求数据时延时800毫秒*/
			// 		var time=setTimeout(function(){
			// 			$(html).find('img').each(function(index){
			// 				loadImage($(this).attr('src'));
			// 			})
			// 			var $newElems = $(html).css({ opacity: 0}).appendTo(container);
			// 			$newElems.imagesLoaded(function(){
			// 				$newElems.animate({ opacity: 1},800);
			// 				container.masonry( 'appended', $newElems,true);
			// 				loading.data("on",true).fadeOut();
			// 				clearTimeout(time);
			// 	        });
			// 		},800)
			// 	}
			// })(sqlJson);
		}
	});
	function loadImage(url) {
	     var img = new Image(); 
	     //创建一个Image对象，实现图片的预下载
	      img.src = url;
	      if (img.complete) {
	         return img.src;
	      }
	      img.onload = function () {
	       	return img.src;
	      };
	 };
	


	// //轮播器初始化
	// $('.banner .tag_img').eq(1).css('display','none');
	// $('.banner .tag_img').eq(2).css('display','none');
	// $('.banner .tag_img').eq(3).css('display','none');
	// $('.banner .tag_img').eq(4).css('display','none');
	// $('.banner .dot span').eq(0).css('color','#E54839');
	// $('.banner strong').html($('.banner .tag_img').eq(0).attr('alt'));

	// //手动轮播器
	// $('.banner .dot span').hover(function(){
	// 	clearInterval(banner_timer); //鼠标移上去停止自动播放

	// 	 if($(this).css('color')!='rgb(51,51,51)' && $(this).css('color')!='#E54839'){  //去掉两张图片切换时底下产生的白边
	// 		banner(this,banner_index==0?$('.banner .dot span').length-1:banner_index-1);  //传this进去
	// 	 }
	// },function(){
	// 	banner_index=$(this).index()+1; //鼠标移开，按最近一次放的位置的下一张图片开始
	// 	banner_timer=setInterval(banner_fn,2000);
	// });

	// //轮播器计数器(初始化)
	// var banner_index=1;

	// //自动轮播器
	// var banner_timer=setInterval(banner_fn,2000);

	// //轮播器共用函数
	// function banner(obj,prev){  //obj=$('#banner ul li').getElement_Base(banner_index)
			
	// 	$('.banner .dot span').css('color','#fff');  //全部初始化，选择哪个在改变
	// 	$(obj).css('color','#E54839');
	// 	$('.banner strong').html($('.banner .tag_img').eq($(obj).index()).attr('alt'));

	// 	$('.banner .tag_img').eq(prev).hide().css('z-index','1');
	// 	$('.banner .tag_img').eq($(obj).index()).show().css('z-index','2');
	// }
	// function banner_fn(){
	// 	if(banner_index >= $('.banner .tag_img').length){
	// 		banner_index=0;
	// 	}
	// 	banner($('.banner .dot span').eq(banner_index).first(),banner_index==0?$('.banner .tag_img').length-1:banner_index-1); //传参进来 $('#banner ul li').getElement_Base(banner_index),加first(),上面接受obj要变成$(obj),利于手动播放时传参this
	// 	banner_index++;
	// }
	
});


$(function(){
	//header好友动态默认
	$('#nav ul').children().eq(1).find('a').css('color','#E54839');

	//评论
	$('#dynamic .comment_btn').click(function(){
		$(this).parent().find('.comment').slideDown();
	});

	//取消评论
	$('#dynamic .cancel').click(function(){
		$(this).parent().parent().slideUp();
	});

	//点击--关注
	$('.who_care .ui-button-text-only .ui-button-text').click(function(){
		$(this).text('已关注');
		$(this).css('color','#E86D44');
	});

	// //谁关注我--默认
	// $('#right_center .who').css('color','#E86D44');

	// //点击--我关注谁
	// $('#right_center .i').click(function(){
	// 	$('#right_center .who').css('color','#777');
	// 	$('#right_center .i').css('color','#E86D44');

	// 	$('#right_center .i_care').show();
	// 	$('#right_center .who_care').hide();
	// });

	// //点击--谁关注我
	// $('#right_center .who').click(function(){
	// 	$('#right_center .i').css('color','#777');
	// 	$('#right_center .who').css('color','#E86D44');

	// 	$('#right_center .who_care').show();
	// 	$('#right_center .i_care').hide();
	// });

	// //点击--更多
	// $('#left .more').click(function(){
	// 	open('showPhoto.html');
	// });

	// //点击--查看全部
	// $('#right_bottom .all').click(function(){
	// 	open('index.html');
	// });

	//点击--头像(左边)
	// $('#left .left').click(function(){
	// 	open('index.html');
	// });
	$('#left .left').hover(function(){
		$(this).next().find('.data').slideDown('slow');
		
	},function(){
		$('#left .data').hide('drop');
	});

	//点击--我的相册--下面的相册
	$('#right_bottom img').click(function(){
		open($(this).attr('url'));
	});

	// //点击--谁关注我--用户名
	// $('#right_center .user_name').click(function(){
	// 	// location='index.html';
	// 	open('index.html');
	// });

	// //点击--用户名--右上角资料
	// $('#right_top .user').click(function(){
	// 	open('index.html');
	// });

	// //点击--相册--我的相册下的相册
	// $('#right_bottom img').click(function(){
	// 	open('showPhoto.html');
	// });


	$('#photo_big1 .left').hover(function(){
		$('.sl').fadeToggle();
	},function(){
		$('.sl').fadeToggle();
	});
	$('#photo_big1 .right').hover(function(){
		$('.sr').fadeToggle();
	},function(){
		$('.sr').fadeToggle();
	});

	//查看大图对话框
	function openDialog(){
		var bh = $(document.body).height();
		var bw = $(document.body).width(); 
		$("#screen").css({ 
			height:bh, 
			width:bw, 
			display:"block"
		}); 
		$("#photo_big1").show(); 
	}

	//关闭对话框
	function closeDialog(){
		$("#screen,#photo_big1").hide();
	}

	$('#photo_big1 .close').click(function(){
		closeDialog();
	});

	$('#dynamic .right .photo img').click(function(){
		openDialog();

		var parent_index = $(this).parents('#dynamic').index();
		// alert(parent_index);
		$('#photo_big1 .left').attr('parent_index',parent_index);
		$('#photo_big1 .right').attr('parent_index',parent_index);

		//创建临时图片对象并保存图片
		setImage($(this).attr('src'));

		var parent = this.parentNode;//$(this).parent().first();
		//设置图片的上一张下一张
		preAndNextImg(parent,$(this).index());
	});

	//创建临时对象并保存图片
	function setImage(src){
		//创建临时图片对象，保存图片
		var tmp = new Image();

		//加载再出现图片，所以src实际赋值放在后面
		$(tmp).bind('load', function () {

			//设置src
			$('#photo_big1 img').attr('src', tmp.src);

			//对img对象进行初始化，因为前一张可能已经对他的位置，宽度进行了设置
			//这里必须清空，否则会影响当前图片的显示位置，大小等
			$('#photo_big1 img').css({
				'margin':'0',
				'height':'auto',
				'width':'auto'
			});
			//设置图片容器的背景色
			//图片切换时，因为loading.gif图片较小，所以将容器的背景设为了白色，
			//这里重新恢复为黑色
			// $('#photo_big1 .img').css('background','#222');
			
			//默认的最大高度和宽度
			var width=480,height=480;
			//按照比例进行缩放
			if(tmp.width>tmp.height&&tmp.width>480){
				height=tmp.height*480/tmp.width;
				var top=(480-height)/2;
				$('#photo_big1 img').css({
					'width':width+'px',
					'height':height+'px',
					'margin-top':top+'px'
				});
			}else if(tmp.height>=tmp.width&&tmp.height>480){
				width=tmp.width*480/tmp.height;
				// $('#photo_big1 .img')里设置了text-align居中，故左右居中不用设置，上下居中需要设置
				// var left=(480-width)/2;
				$('#photo_big1 img').css({
					'width':width+'px',
					'height':height+'px',
					// 'margin-left':left+'px'
				});
			}else{
				//如果长度都小于480，则按照原来的大小显示
				//top设置图片居中显示
				var top=(480-tmp.height)/2;
				$('#photo_big1 img').css('margin-top',top+'px');
			}
		});

		//解决IE第二次刷新只加载不出现图片问题(这行放下面，不能放上面)
		tmp.src=src;  //src属性可以在后台加载这张
	}

	//图片的上一张下一张先缓存
	//parent 图片的外容器
	//index 当前图片的索引值  第几个..
	function preAndNextImg(parent,index){
		index=parseInt(index);
		// 图片的数量
		var length = $(parent).find('img').length;
		//获取上一张下一张的索引值
		var pre = index==0?length-1:index-1;
		var next = index==length-1?0:index+1;
		//创建两个临时图片对象
		var pre_img = new Image();
		var next_img = new Image();

		//设置缓存对象的图片路径
		pre_img.src = $(parent).find('img').eq(pre).attr('src');
		next_img.src = $(parent).find('img').eq(next).attr('src');

		//将图片的上下张的src和索引值index存在左右两边的span
		//以便于点击左右两边的span可以直接获取图片的路径
		$('#photo_big1 .left').attr('src',pre_img.src);
		$('#photo_big1 .right').attr('src',next_img.src);
		$('#photo_big1 .left').attr('index',pre);
		$('#photo_big1 .right').attr('index',next);

		//设置图片的计数器
		$('#photo_big1 .count').html((++index)+'/'+length);
	}

	$('#photo_big1 .left').click(function(){
		//避免图片还没有加载完成，在切换时，先设置图片为loading.gif
		// $('#photo_big1 img').attr('src', 'image/loading.gif').css({
		// 	'width':'32px',
		// 	'height':'32px',
		// 	'top':'190px'
		// });
		// $('#photo_big1 .img').css('background','#fff');

		setImage($(this).attr('src'));

		var index = $(this).attr('index');
		var parent_index=$(this).attr('parent_index');
		var parent = $('#dynamic .right .photo').eq(parent_index).first();
		// //设置图片的上一张下一张
		preAndNextImg(parent,index);
	});

	$('#photo_big1 .right').click(function(){
		//避免图片还没有加载完成，在切换时，先设置图片为loading.gif
		// $('#photo_big1 img').attr('src', 'image/loading.gif').css({
		// 	'width':'32px',
		// 	'height':'32px',
		// 	'top':'190px'
		// });		
		// $('#photo_big1 .img').css('background','#fff');

		setImage($(this).attr('src'));

		var index = $(this).attr('index');
		var parent_index=$(this).attr('parent_index');
		var parent = $('#dynamic .right .photo').eq(parent_index).first();
		// //设置图片的上一张下一张
		preAndNextImg(parent,index);
	});



});
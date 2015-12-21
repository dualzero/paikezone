
$(function(){
    if(!window.detail){
        alert(123);
    }

	//header我的地区默认
	$('#nav .father').eq(0).css('color','#E54839');

	index=0;
	$('.tab li').eq(0).css('color','#5DB1F0');
	changeImg($('.tab ul li').eq(0));

	//tab鼠标移入移出
	$('.tab li').hover(function(){
		//将图片地址换成新地址
		if($('.tab li').index($(this))!=index){
			$(this).css('color','#5DB1F0');
			changeImg($(this));
		}
	},function(){
		//将图片地址换成原来的地址
		if($('.tab li').index($(this))!=index){
			$(this).css('color','#555');
			changeImg($(this));
		}
	});

	//tab鼠标点击
	$('.tab li').click(function(){
		//将原来选定的设置去除
		if($('.tab li').index($(this))!=index&&$('.tab li').index($(this))!=2){

			$('.tab li').eq(index).css('color','#555');
			changeImg($('.tab ul li').eq(index));

			$(this).css('color','#5DB1F0');
			//设置当前索引
			index=$('.tab li').index($(this));
		}
	});

	$('#photo_list .list:nth-child(6n+1)').css('margin-left','5px');

	$('.tab li').eq(0).click(function(){
		$('#photo_list .list .photo img').css('margin',0);
		$('#photo_list .list').css('margin','15px 0 0 15px');
		$('#photo_list .list:nth-child(6n+1)').css('margin-left','5px');
		$('#photo_list .list .photo').css('width','100px');
		$('#photo_list .list .photo').css('height','100px');
		var img = $('#photo_list .list .photo img');
		img.css('width','100px');
		img.css('height','100px');
		img.each(function(){
			$(this).attr('src',$(this).attr('thumb'));
		});
		$('#photo_list .list .info').hide();
	});

	$('.tab li').eq(1).click(function(){
		$('#photo_list .list').css('margin','25px 0 0 35px');
		$('#photo_list .list:nth-child(3n+1)').css('margin-left','5px');
		$('#photo_list .list .photo').css({
			'width':'200px',
			'height':'200px'
		});
		var img = $('#photo_list .list .photo img');
		// img.css('width','200px');
		// img.css('height','200px');
		img.each(function(){
			var self = $(this);
			var path = self.attr('path');
			self.attr('src',path);
			//new一个图片对象
			var tmp = new Image();
			//图片夹在完成后的操作
			$(tmp).load(function(){
				var width  = tmp.width;
				var height = tmp.height;
				//按比例显示在200*200的框里面
				if(width > height){
					height = 200/width*height;
					width = 200;
					self.css('margin-top',200-height+'px');
				}else{
					width = 200/height*width;
					height = 200;
					self.css('margin-left',(200-width)/2+'px');
				}
				self.css({
					'width':width+'px',
					'height':height+'px',
				});
			});
			//解决IE第二次刷新只加载不出现图片问题(这行放下面，不能放上面)
			tmp.src = path;//src属性可以在后台加载这张
		});
		$('#photo_list .list .info').show();
	});

    window.exist_swiper = false;
	$('.tab li').eq(2).click(function(){
        if(window.exist_swiper == false){
            setSwiper();
        }else{
            $('.swiper-part').show();
        }
    });


	//点击--我的相册--下面的相册
	$('#right_bottom img').click(function(){
		location = $(this).attr('url');
	});

	//大图显示
	$('.list .photo img').click(function(){
        $('.big-img img').attr('src',$(this).attr('path'));
        $('.big-img').show();
        $('.screen').show();
    });
    $('.big-img,.screen').click(function(){
        $('.big-img').hide();
        $('.screen').hide();
    });
})

//收藏
$(function(){
    $('.collect-add').click(function(){
        var self = $(this);
        var pic_id = self.attr('value');
        $.post(self.attr('url'),{pic_id:pic_id},function(data){
            alert(data.info);
            if(data.status){  //收藏成功
                self.parent().find('.collect-add').hide();
                self.parent().find('.collect-cancle').show();
            }
        });
    });
    $('.collect-cancle').click(function(){
        var self = $(this);
        var pic_id = self.attr('value');
        $.post(self.attr('url'),{pic_id:pic_id},function(data){
            alert(data.info);
            if(data.status){  //收藏成功
                self.parent().find('.collect-add').show();
                self.parent().find('.collect-cancle').hide();
            }
        });
    });
})

//swiper
function setSwiper(){

    window.exist_swiper = true;

    $('.swiper-part').show();

	var pic_swiper_h = $(window).height()-150;
	$('.pic-container').css('height', pic_swiper_h + 'px');

    //获取所有的图片
    var pic_list = [];
    var thumb_list = [];
    var i = 0;
    $('.list .photo img').each(function(){
        pic_list[i] = $(this).attr('path');
        thumb_list[i] = $(this).attr('thumb');
        i++;
    });

    //共有i张图片,为两个swiper建立i个swiper-slide
    var pic_str = '',thumb_str='';
    for(var k = 0; k < i; k ++){
        //设置大图的swiper
        var count = '<span class="count">'+(k+1)+' / '+i+'</span>';//计数器
        var pic_content = '<img data-src="'+pic_list[k]+'" class="swiper-lazy"><div class="swiper-lazy-preloader"></div>'; //大图swiper的内容
        pic_str += '<div class="swiper-slide">'+pic_content+count+'</div>';
        //设置小图的swiper
        var thumb_content = '<img src="'+thumb_list[k]+'">';//小图swiper的内容
        thumb_str += '<div class="swiper-slide" value="'+k+'">'+thumb_content+'</div>';
    }
    $('.pic-container .swiper-wrapper').html(pic_str);
    $('.thumb-container .swiper-wrapper').html(thumb_str);


    var pic_swiper = new Swiper('.pic-container', {
        spaceBetween: 30,
        lazyLoading : true,
        simulateTouch : false,
        updateOnImagesReady:true,
        onLazyImageLoad: function(swiper, slide, image){
            var max = $(slide).height()*0.9;
            var src = $(image).attr('data-src');
            var tmp = new Image();
            $(tmp).load(function(){
                var width = tmp.width;
                var height = tmp.height;
                //缩小图片的大小
                if(width > max || height > max){
                    if(width > height){
                        height = max/width*height;
                        width = max;
                    }else{
                        width = max/height*width;
                        height = max;
                    }
                    $(image).css({
                        'width' : width,
                        'height' : height
                    });
                }
            });
            tmp.src = src;
        }
    });
    var thumb_swiper = new Swiper('.thumb-container', {
        paginationClickable: true,
        centeredSlides: true,
        slidesPerView: 7,
        spaceBetween: 20,
        onSlideChangeStart: function(swiper){
            pic_swiper.slideTo(thumb_swiper.activeIndex);
        }

    });

    //缩略图的点击事件
    $(document).on('click','.thumb-container .swiper-slide',function(){
        var index = $(this).attr('value');
        thumb_swiper.slideTo(index);
    });
    //上一张的点击事件
    $('.swiper-left').click(function(){
        var index = thumb_swiper.activeIndex;
        var length = thumb_swiper.slides.length;
        if(index == 0){
            thumb_swiper.slideTo(length-1);
        }else{
            thumb_swiper.slideTo(index-1);
        }
    });
    //下一张的点击事件
    $('.swiper-right').click(function(){
        var index = thumb_swiper.activeIndex;
        var length = thumb_swiper.slides.length;
        if(index == length-1){
            thumb_swiper.slideTo(0);
        }else{
            thumb_swiper.slideTo(index+1);
        }
    });

    //关闭事件
    $('.fa-close').click(function(){
        $('.swiper-part').hide();
    });
}
function changeImg(li){
	var xsrc=li.find('img').attr('xsrc');
	var src=li.find('img').attr('src');
	li.find('img').attr('src',xsrc);
	li.find('img').attr('xsrc',src);
}
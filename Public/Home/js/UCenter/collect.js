$(function () {
	//header我的地区默认
	$('#nav .father').eq(0).css('color','#E54839');
	
	//tab默认
	$('#tab ul li').eq(2).find('a').css('color','#E54839');
	$('#tab ul li').eq(2).find('a').css('border-bottom-color','white');

	$('.list:nth-child(4n+1)').css('margin-left','5px');

	//把图片按照原来的比例显示
	var img = $('.list .photo img');
	// img.css('width','200px');
	// img.css('height','200px');
	img.each(function(){
		var self = $(this);
		var src = self.attr('src');
		//new一个图片对象
		var tmp    = new Image();
		tmp.src    = src;
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

	//点击--用户名
	$('.list .info .user').click(function(){
		open($(this).attr('url'));
	});

	//点击--所在相册
	$('.list .info .album').click(function(){
		open($(this).attr('url'));
	});

	$('.cancle').click(function(){
        var self = $(this);
        var url = self.attr('url');
        var pic_id = self.attr('value');
        $.post(url,{pic_id:pic_id},function(data){
            alert(data.info);
            if(data.status){  //取消收藏成功
                location.reload();
                self.find('.collect-add').show();
                self.find('.collect-cancle').hide();
            }
        });
    });

    $('.list .photo img').click(function(){
        $('.big-img img').attr('src',$(this).attr('src'));
        $('.big-img').show();
        $('.screen').show();
    });
    $('.big-img,.screen').click(function(){
        $('.big-img').hide();
        $('.screen').hide();
    });
});
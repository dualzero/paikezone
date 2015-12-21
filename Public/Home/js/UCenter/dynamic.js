$(function () {
	//header我的地区默认
	$('#nav .father').eq(0).css('color','#E54839');
	
	//tab默认
	$('#tab ul li').eq(1).find('a').css('color','#E54839');
	$('#tab ul li').eq(1).find('a').css('border-bottom-color','white');

	$('.dyn .photo img').click(function(){
		var src = $(this).attr('xsrc');
		$('.big-img img').attr('src',src);
		$('.big-img img').css({
			'max-height':$(window).height()*0.85,
			'max-width':$(window).height()*0.85,
		});
		$.fancybox.open('#show-img', {
            closeBtn: true,
            // maxWidth: $(window).height()*0.5,
            // minWidth: $(window).height()*0.5,
            // maxHeight: $(window).height()*0.5,
            // minHeight: $(window).height()*0.5,
            padding: 12,
            autoCenter : true,
            openEffect: 'fade',
            closeEffect: 'fade',
            helpers: {
                overlay : {
                    closeClick : false
                }
            }
        });
	});
});
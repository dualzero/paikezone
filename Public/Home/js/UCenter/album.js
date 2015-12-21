$(function () {
	//header我的地区默认
	$('#nav .father').eq(0).css('color','#E54839');
	
	//tab--默认
	$('#tab ul li').eq(0).find('a').css('color','#E54839');
	$('#tab ul li').eq(0).find('a').css('border-bottom-color','white');

	//我的相册--间距
	$('#album .list:nth-child(5n+2)').css('margin-left','10px');

	//遮罩层的高度
	$('.screen').css('height',$('body').height()+'px');

	//点击--相册的图片，评论
	$('.list img, .list .comment').click(function(){
		var url = $(this).parents('.list').attr('url');
		open($(this).parents('.list').attr('url'));
	});

	//按钮--创建相册
	$('#album .new').click(function(){
		$('#create-album').show();
		$('.screen').show();
	});
	//创建相册窗体的关闭按钮
	$('#create-album .fa-close').click(function(){
		$('#create-album').hide();
		$('.screen').hide();
	});
	//创建/编辑相册的提交事件
	$(".create-form,.edit-form").submit(function(){
		var self = $(this);
		console.log(self.serialize());
		$.post(self.attr("action"), self.serialize(), success, "json");
		return false;
		function success(data){
			console.log(data);
			if(data.status){
                self.find(".check-tip").find('span').css('color','green').text(data.info);
                setTimeout(function(){
                    window.location.href = data.url;
                },1500);
			} else {
				self.find(".check-tip").find('span').css('color','red').text(data.info);
				//刷新验证码
				$(".reloadverify").click();
			}
		}
	});

	//显示用户信息
	$('#top .info .user').click(function(){
		 $('#mydata').dialog('open');
	});

	//上传图片
	var ue = UE.getEditor('ueditor',{
		toolbars:[
			['insertimage']
		]}
	);
	ue.ready(function(){
		this.hide();
	});

	$('.upload').click(function(){
		showImageDialog(ue);
	})

	ue.addListener('contentChange', function (editor) {
		//获取编辑器中的内容（html 代码）
		var imgs = ue.getPlainTxt();
		//清空编辑器中的内容，以便下一次添加图片。
		ue.execCommand('cleardoc');
		$(".temp-img-list").html(imgs).hide();
		var img_arr = new Array();
		$(".temp-img-list img").each(function(){
			var src = $(this).attr("src");
			img_arr.push(src);
		});

		//post上传图片
		var url = Think.ROOT + '/Home/UCenter/upload_pic';
		info = {
			img_list : img_arr,
			album_id : $('#edui3_iframe').contents().find('.upload-album').attr('value')
		}
		$.post(url, info, success, 'json');
		function success(data){
			alert(data.info);
			location.reload();		
		}
   });
});

// 相册设置--设置封面   设置相册
$(function () {
	// 鼠标移到相册上、显示下拉的标志
	$('#album .list').hover(function(){
		$(this).find('.edit').show();
	},function(){
		$(this).find('.edit').hide();
		$(this).find('.edit-list').hide();
	});
	//点击下拉框--显示设置的选项
	$('#album .list .fa-chevron-down').click(function(){
		$(this).next().toggle();
	});

	//点击--编辑
	$('.edit-item').click(function(){
		//获取相册信息--post向服务器请求
		var id  = $(this).parent().attr('value');
		var url = $(this).attr('url');
		$.post(url,{id:id},function(data){
			//把获取到的数据写入弹出框
			console.log(data);
			$('.edit-form').find('input[name="id"]').val(data.id);
			$('.edit-form').find('input[name="name"]').val(data.name);
			$('.edit-form').find('textarea[name="description"]').val(data.description);
			$('.edit-form').find('select[name="type"]').val(data.tid);
			$('.edit-form').find('select[name="limit"]').val(data.limit);
		});
		$('#edit-album').show();
		$('.screen').show();
	});

	//点击--删除
	$('.del-item').click(function(){
		
		if(!confirm('删除相册后将会删除所有相关的信息。确认删除相册吗？')){
			return false;
		}
		//获取相册信息--post向服务器请求
		var id  = $(this).parent().attr('value');
		var url = $(this).attr('url');
		$.post(url,{id:id},function(data){
			if(data.status){
				alert(data.info);
				location.reload();
			}else{
				alert('删除失败');
			}
		});
	});

	//点击--设置封面
	$('.cover-item').click(function(){
		//通过相册id获取相册的所有图片
		var id  = $(this).parent().attr('value');
		var url = $(this).attr('url');
		$.post(url,{id:id},function(data){
			console.log(data.info);
			if(data.status){
				//弹出窗体
				var list = data.info;
				var html = '';
				for(var i in list){
					html += '<div class="pic" value="'+list[i].id+'"><img src="'+list[i].path+'"></div>'
				}
				$('#set-cover input[name="id"]').val(id);
				$('#set-cover .imgs').html(html);
				$('#set-cover').show();
				$('.screen').show();
			}else{
				alert(data.info);
			}
		});
	});

	//设置封面里面的图片点击事件
	$(document).on('click','#set-cover .pic',function(){
		$('#set-cover .pic').removeClass('current');
		$(this).addClass('current');
		$('#set-cover input[name="cover_id"]').val($(this).attr('value'));
	});

	//设置封面
	$(".set-cover-form").submit(function(){
		var self = $(this);
		var cover_id = $('#set-cover input[name="cover_id"]').val();
		if(!cover_id){
			alert('请先选择图片！');
			return false;
		}
		// console.log(self.serialize());
		$.post(self.attr("action"), self.serialize(), success, "json");
		return false;
		function success(data){
			console.log(data);
			alert(data.info);
			location.reload();
		}
	});

	//关闭弹窗
	$('#set-cover .fa-close').click(function(){
		$('#set-cover').hide();
		$('.screen').hide();
		$('#set-cover .imgs').html('');
	});

	//编辑相册窗体的关闭按钮
	$('#edit-album .fa-close').click(function(){
		$('#edit-album').hide();
		$('.screen').hide();
	});
})

//ueditor设置上传图片的一系列显示方式
function showImageDialog(ue){
	ue.getDialog('insertimage').open();
	//隐藏确认取消按钮
	$('#edui3_body').css('height','450px');
	$('.edui-dialog-foot').hide();
	$
	//修改显示的内容
	$('.edui-dialog-caption').text('上传照片');
	//移除点击弹出窗旁边，上传窗体会关闭
	$('#edui4').attr('onmousedown','').attr('onclick','');
	//移除关闭按钮的一系列事件
	$('#edui6_body').attr('onmousedown','').attr('onclick','');
	//把 关闭按钮 点击事件关联到下方 确认 的点击事件
 	$(document).on('click','#edui6_body',function(){
		$('#edui7_body').click();
	});
	document.getElementById("edui3_iframe").onload = function(){
		//设置’请选择相册‘旁边的默认图片
		var album_list = $('#edui3_iframe').contents().find('.album-list');
		//获取所有的相册列表--遍历相册列表
		var str = '';
		$('#album .list').each(function(){
			var id   = $(this).attr('value');
			var src  = Think.ROOT + '/' + $(this).find('img').attr('src');
			var name = $(this).find('.name').text();
			str += '<li value="'+id+'"><img src="'+src+'"><span>'+name+'</span></li>';
		});
		album_list.find('ul').html(str);
	};
}

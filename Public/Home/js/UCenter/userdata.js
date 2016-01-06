$(function(){
    //header我的地区默认
    $('#nav .father').eq(0).css('color','#E54839');
    
    //tab--默认
    $('#tab ul li').eq(3).find('a').css('color','#E54839');
    $('#tab ul li').eq(3).find('a').css('border-bottom-color','white');

    //关闭弹窗
    $('.fa-close').click(function(){
        $('.dialog').hide();
        $('.screen').hide();
    });

    //提交裁剪好的图片
    $('.save-pic').click(function(){
        if($('#preview-hidden').html() == ''){
            alert('请先上传图片！');
        }else{
            //由于GD库裁剪gif图片很慢，所以长时间显示弹出框
            setTimeout(function(){
                 $('#pic').submit(function(){
                    var self = $(this);
                    $.post(self.attr("action"), self.serialize(), success, "json");
                    return false;
                    function success(data){
                        console.log(data);
                        if(data.status){
                            alert(data.info);
                        }else{
                            alert('上传失败');
                        }
                        location.reload();
                    }
                 });
                 $('#pic').submit();
             },1000);
        }
    });
    $('.modify').click(function(){
        $('.dialog').show();
        $('.screen').show();
    });
    //重新上传,清空裁剪参数
    var i = 0;
    $('.reupload-img').click(function(){
        $('#preview-hidden').find('*').remove();
        $('#preview-hidden').hide().addClass('hidden').css({'padding-top':0,'padding-left':0});
        $('.crop img').attr('src', '');
    });
    //上传头像(uploadify插件)
    $("#user-pic").uploadify({
        'queueSizeLimit' : 1,
        'removeTimeout' : 0.5,
        'preventCaching' : true,
        'multi'    : false,
        'swf'           : 'Public/Home/js/UCenter/uploadify-v3.1/uploadify.swf',
        'uploader'      : $('#user-pic').attr('url'),
        'buttonText'    : '<i class="fa fa-plus-square"></i>上传头像',
        'width'         : '200',
        'height'        : '200',
        'fileTypeExts'  : '*.jpg; *.png; *.gif;',
        'onUploadSuccess' : function(file, data, response){
            //调试语句
            console.log(data);

            var data = $.parseJSON(data);
            if(data['status'] == 0){
                alert(data.info);
                return;
            }
            var preview = $('.upload-area').children('#preview-hidden');
            preview.show().removeClass('hidden');
            //两个预览窗口赋值
            $('.crop').children('img').attr('src',data['path']+'?random='+Math.random());
            //隐藏表单赋值
            $('#img_src').val(data['path']);
            //绑定需要裁剪的图片
            var img = $('<img />');
            preview.append(img);
            preview.children('img').attr('src',data['path']+'?random='+Math.random());
            var crop_img = preview.children('img');
            crop_img.attr('id',"cropbox").show();
            var img = new Image();
            img.src = data['path']+'?random='+Math.random();
            //根据图片大小在画布里居中
            img.onload = function(){
                var img_height = 0;
                var img_width = 0;
                var real_height = img.height;
                var real_width = img.width;
                if(real_height > real_width && real_height > 200){
                    var persent = real_height / 200;
                    real_height = 200;
                    real_width = real_width / persent;
                }else if(real_width > real_height && real_width > 200){
                    var persent = real_width / 200;
                    real_width = 200;
                    real_height = real_height / persent;
                }
                if(real_height < 200){
                    img_height = (200 - real_height)/2;
                }
                if(real_width < 200){
                    img_width = (200 - real_width)/2;
                }
                preview.css({width:(200-img_width)+'px',height:(200-img_height)+'px'});
                preview.css({paddingTop:img_height+'px',paddingLeft:img_width+'px'});
            }
            //裁剪插件
            $('#cropbox').Jcrop({
                bgColor:'#333',   //选区背景色
                bgFade:true,      //选区背景渐显
                fadeTime:1000,    //背景渐显时间
                allowSelect:false, //是否可以选区，
                allowResize:true, //是否可以调整选区大小
                aspectRatio: 1,     //约束比例
                minSize : [100,100],//可选最小大小
                boxWidth : 200,     //画布宽度
                boxHeight : 200,    //画布高度
                onChange: showPreview,//改变时重置预览图
                onSelect: showPreview,//选择时重置预览图
                setSelect:[ 0, 0, 150, 150],//初始化时位置
                onSelect: function (c){ //选择时动态赋值，该值是最终传给程序的参数！
                    $('#x').val(c.x);//需裁剪的左上角X轴坐标
                    $('#y').val(c.y);//需裁剪的左上角Y轴坐标
                    $('#w').val(c.w);//需裁剪的宽度
                    $('#h').val(c.h);//需裁剪的高度
              }
            });
         }
    });
    //预览图
    function showPreview(coords){
        var img_width = $('#cropbox').width();
        var img_height = $('#cropbox').height();
          //根据包裹的容器宽高,设置被除数
          var rx = 100 / coords.w;
          var ry = 100 / coords.h;
          $('#crop-preview-100').css({
            width: Math.round(rx * img_width) + 'px',
            height: Math.round(ry * img_height) + 'px',
            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
            marginTop: '-' + Math.round(ry * coords.y) + 'px'
          });
          rx = 60 / coords.w;
          ry = 60 / coords.h;
          $('#crop-preview-60').css({
            width: Math.round(rx * img_width) + 'px',
            height: Math.round(ry * img_height) + 'px',
            marginLeft: '-' + Math.round(rx * coords.x) + 'px',
            marginTop: '-' + Math.round(ry * coords.y) + 'px'
          });
    }

    $('.save-btn').click(function(){
        var url = $(this).attr('url');
        var uid = $('.uid').val();
        var sex = $('#sex').val();
        var birthday = $('#birthday').val();
        var sign = $('#sign').val();
        var info = {uid:uid,sex:sex,birthday:birthday,sign:sign};
        $.post(url,info,function(data){
            console.log(data);
            if(data.status == 1){
                alert('保存成功！');
                location.reload();
            }
        },'json');
    });

    $('.change-btn').click(function(){
        var url = $(this).attr('url');
        var uid = $('.uid').val();
        var old_psd = $('#old_psd').val(); //旧密码
        var new_psd = $('#new_psd').val(); //新密码
        var re_psd = $('#re_psd').val();   //重复密码
        var info = {uid:uid,old_psd:old_psd,new_psd:new_psd,re_psd:re_psd};
        $.post(url,info,function(data){
            console.log(data);
            if(data.status == 1){
                alert('修改成功，请重新登录系统');
                location = data.url;
            }else{
                alert(data.info);
            }
        },'json');
    });
});
$(function(){

    // api用于裁剪、width、height用于显示当前尺寸
    var api, real_width, real_height, show_width, show_height;
    //获取图片的信息
    var src = $('.edit img').attr('src');
    var tmp = new Image();
    tmp.src = src;
    $(tmp).load(function(){
        show_width = real_width = tmp.width;
        show_height = real_height = tmp.height;
        $('.real-size').html(real_width + ' x ' + real_height);
        var w = tmp.width;
        var h = tmp.height;
        if(w >= h && w > 300){
            h = 300 / w * h;
            w = 300;
        }
        if(h > w && h > 300){
            w = 300 / h * w;
            h = 300;
        }
        show_width = w;
        show_height = h;
        $('.edit img').css({
            'width' : w + 'px',
            'height': h + 'px'
        });
    });

    //设置参数
    $('.fa-rotate-left').click(function(){
        var value = (parseInt($(this).attr('value')) + 90) % 360;
        $(this).attr('value', value);
        imageEdit();
    });
    $('.fa-rotate-right').click(function(){
        var value = (parseInt($(this).attr('value')) - 90) % 360;
        $(this).attr('value', value);
        imageEdit();
    });
    $('.fa-minus').click(function(){
        var value = (parseFloat($(this).attr('value')) - 0.1).toFixed(1);
        //最大可以放大或者缩小50%
        var zoom = (value - 0 + parseFloat($('.fa-plus').attr('value'))).toFixed(1);
        if(zoom < -0.5 || zoom > 0.5){
            alert('图片最大可以放大或者缩写50%');
            return;
        }
        $(this).attr('value', value);
        imageEdit();
    });
    $('.fa-plus').click(function(){
        var value = (parseFloat($(this).attr('value')) + 0.1).toFixed(1);
        //最大可以放大或者缩小50%
        var zoom = (value - 0 + parseFloat($('.fa-minus').attr('value'))).toFixed(1);
        if(zoom < -0.5 || zoom > 0.5){
            alert('图片最大可以放大或者缩写50%');
            return;
        }
        $(this).attr('value', value);
        imageEdit();
    });

    // 旋转缩放图片
    function imageEdit(){
        // 点击之后，如果正在裁剪，则把裁剪的参数删除掉
        if(api){
            api.destroy();
            api = '';
        }
        //获取参数
        var src = $('.edit img').attr('src').split('?')[0];
        var rotate1 = $('.fa-rotate-left').attr('value');
        var rotate2 = $('.fa-rotate-right').attr('value');
        var zoom1 = $('.fa-minus').attr('value');
        var zoom2 = $('.fa-plus').attr('value');
        var rotate = parseInt(rotate1) + parseInt(rotate2);
        var zoom = (parseFloat(zoom1) + parseFloat(zoom2) + parseInt(1)).toFixed(1);
        var info = {
            rotate  : rotate,
            zoom    : zoom,
            tmp_url : src
        };
        var url = $('.op').attr('url');
        $.post(url,info,function(data){
            //重新加载图片
            $('.edit img').attr('src', src + '?random='+Math.random());
            //修改图片的信息
            var w1 = parseInt(real_width * zoom);
            var h1 = parseInt(real_height * zoom);
            var w2 = parseInt(show_width * zoom);
            var h2 = parseInt(show_height * zoom);
            //获取图片本来显示的大小
            if(rotate % 180){  //不等于0旋转90 或者 270
                $('.real-size').html(h1 + ' x ' + w1);
                $('.edit img').css({
                    'width' : h2 + 'px',
                    'height': w2 + 'px'
                });
            }else{
                $('.real-size').html(w1 + ' x ' + h1);
                $('.edit img').css({
                    'width' : w2 + 'px',
                    'height': h2 + 'px'
                });
            }
        });
    }

    $('.fa-cut').click(function(){
        //如果已经在裁剪，则删掉重新建立
        if(api){
            api.destroy();
            api = '';
            return ;
        }
        api = $.Jcrop('#cropbox',{
            bgColor:'#333',   //选区背景色
            bgFade:true,      //选区背景渐显
            fadeTime:1000,    //背景渐显时间
            allowSelect:false, //是否可以选区，
            allowResize:true, //是否可以调整选区大小
            // aspectRatio: 1,     //约束比例
            minSize : [100,100],//可选最小大小
            // boxWidth : 200,     //画布宽度
            // boxHeight : 200,    //画布高度
            setSelect:[ 0, 0, 150, 150],//初始化时位置
            onSelect: function (c){ //选择时动态赋值，该值是最终传给程序的参数！
                if(real_width > real_height){
                    ratio = 300 / real_width;
                }else{
                    ratio = 300 /real_height;
                }
                $('#x').val((c.x / ratio).toFixed(0));//需裁剪的左上角X轴坐标
                $('#y').val((c.y / ratio).toFixed(0));//需裁剪的左上角Y轴坐标
                $('#w').val((c.w / ratio).toFixed(0));//需裁剪的宽度
                $('#h').val((c.h / ratio).toFixed(0));//需裁剪的高度
            }
        });
    });

    $('form').submit(function(){
        var self = $(this);
        if(!confirm('确认保存编辑的图片吗？')){
            return false;
        }
        var info = '';
        if(api){
            info = self.serialize();
        }else{
            info = {
                src : $('#img_src').attr('value')
            };
        }
        console.log(info);
        $.post(self.attr('action'),info,function(data){
            if(data.status){
                alert(data.info);
                //更新浏览器缓存
                location.reload();
            }
        });
        return false;
    });
});
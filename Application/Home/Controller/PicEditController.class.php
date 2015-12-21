<?php

namespace Home\Controller;
use Think\Controller;
use Think\Upload;
use Vendor\ThinkImage\ThinkImage;

class PicEditController extends HomeController{

    //初始化
    protected function _initialize(){

        parent::_initialize();
        /**
         * 获取用户的信息
         */
        //获取用户id
        $user_id = session('user_auth')['uid'];
        I('uid') && $user_id = I('uid');//如果没有传用户id，则获取的是自己的资料，否则获取的是其他人的用户资料
        //获取用户的具体信息
        $map = array();
        $map['uid'] = $user_id;
        $user = M('User')->where($map)->find();
        //获取共有几个相册
        $user['album_num'] = M('Album')->where($map)->count();
        //获取共有几张照片
        $user['pic_num'] = M('Pic')->where($map)->count();
        //获取收藏
        $user['collect_num'] = M('Collect')->where($map)->count();
        $this->assign('user',$user);

        //判断查看的是登录者的信息 ，，，还是别人的信息
        if($user_id != session('user_auth')['uid']){
            $this->assign('user_self',false);
        }else{
            $this->assign('user_self',true);
        }
    }

    public function edit(){
        // 获取图片id
        $map = array('id' => I('pic_id'));
        $pic = M('Pic')->where($map)->find();
        //复制一张缓存图片--用于处理
        $new_path = str_replace('.', '_tmp.', $pic['path']);
        copy($pic['path'], $new_path);
        // Content type
        
        // flip($new_path,$new_path,180);
        $pic['path'] = $new_path;
        $this->assign('pic', $pic);
        $this->display();
    }

    //旋转、缩放图片
    public function image_edit(){
        $rotate = I('rotate');
        $zoom = I('zoom');
        $tmp_url = I('tmp_url');  // 这个只是缓存的图片路径
        /**
         * 因为连续的对图片进行旋转、缩放等，图片的质量会下降
         * 所以每次都是获取要操作的参数，然后拿原来的图片重新复制一张缓存图片，对其操作
         */
        $real_url = str_replace('_tmp.', '.', $tmp_url);
        copy($real_url, $tmp_url);
        // 执行函数
        // imgEdit($tmp_url, $rotate, $zoom);
        if($rotate){
            img_rotate($tmp_url, $rotate);
        }
        if($zoom != 1){
            img_zoom($tmp_url, $zoom);
        }
    }

    // 保存图片
    public function save(){
        $params = I('post.');
        $tmp_url = $params['src'];
        //得到真实图片的的路径
        $real_url = str_replace('_tmp.', '.', $tmp_url);
        $thumb_url = str_replace('Picture', 'Thumb', $real_url);
        // 如果这些参数存在说明有裁减图片
        if(isset($params['w']) && isset($params['h']) && isset($params['x']) && isset($params['y'])){
            //实例化裁剪类
            $Think_img = new ThinkImage(THINKIMAGE_GD);
            //裁剪原图得到选中区域
            $Think_img->open($tmp_url)->crop($params['w'],$params['h'],$params['x'],$params['y'])->save($tmp_url);
            //同时重新生成缩略图
            $Think_img->open($tmp_url)->thumb(150,150, 1)->save($thumb_url);
        }
        // 复制一份缓存图片保存为原图，然后删除缓存图片
        copy($tmp_url, $real_url);
        unlink($tmp_url);
        $this->success('保存成功');
    }
}
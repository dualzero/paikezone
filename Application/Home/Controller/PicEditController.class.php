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
        $old_real_url = str_replace('_tmp.', '.', $tmp_url);
        $old_thumb_url = str_replace('Picture', 'Thumb', $old_real_url);

        $file_name = pathinfo($old_real_url)['filename'];
        $new_name = time() . rand(100000,999999);

        //给原图和缩略图都重新命名--避免缓存
        $rand = rand();
        $new_pic_path = str_replace($file_name, $new_name, $old_real_url);
        $new_thumb_path = str_replace('Picture', 'Thumb', $new_pic_path);
        
        //更新图片表(Pic)---更新缩略图表(Thumb)
        $map = array('path' => $old_real_url);
        M('Pic')->where($map)->setField('path', $new_pic_path);
        $map = array('path' => $old_thumb_url);
        M('Thumb')->where($map)->setField('path', $new_thumb_path);

        //实例化裁剪类
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        // 如果这些参数存在说明有裁减图片
        if(isset($params['w']) && isset($params['h']) && isset($params['x']) && isset($params['y'])){
            //裁剪原图得到选中区域
            $Think_img->open($tmp_url)->crop($params['w'],$params['h'],$params['x'],$params['y'])->save($tmp_url);
        }
        //同时重新生成缩略图
        $Think_img->open($tmp_url)->thumb(150,150, 3)->save($new_thumb_path);
        // 复制一份缓存图片保存为原图，然后删除缓存图片
        copy($tmp_url, $new_pic_path);
        unlink($tmp_url);
        $this->success('保存成功');
    }

    public function del(){

        $pic_id = I('pic_id');

        $result = false;

        /**
         * 删除动态记录
         */
        //先获取到图片所对应的相册信息
        $map = array('id' => $pic_id);
        $album_id = M('Pic')->where($map)->getField('album_id');
        //如果相册的封面是要删除的这张图片，那就把相册id设置为0
        $album = M('Album')->find($album_id);
        if($album['cover_id'] == $pic_id){
            $map = array('id' => $album_id);
            M('Album')->where($map)->setField('cover_id',0);
        }

        //获取这个相册的所有动态
        $map = array('album_id' => $album_id);
        $dyn_list = M('Dynamic')->where($map)->select();
        //循环找出这张图片出现的动态--记录这条动态的id
        $dyn_id = '';
        $pic_ids = '';
        foreach ($dyn_list as $key => $dyn) {
            if(strstr($dyn['pic_ids'], $pic_id)){
                $dyn_id = $dyn['id'];
                $pic_ids = $dyn['pic_ids'];
            }
        }
        //找出动态id后，对这条动态的pic_ids进行处理
        //1.如果这条动态只上传了要删除的这张图片，则直接删除这条动态
        //2.否则把要删除的id从pic_ids中删掉
        if($pic_ids == $pic_id){
            $map = array('id' => $dyn_id);
            M('Dynamic')->where($map)->delete();
        }else{
            //把1,2,3字符串变成数组array(1,2,3)
            $ids_arr = explode(',',$pic_ids);
            //循环找出要删除的那个值
            foreach ($ids_arr as $key => $val) {
                if($val == $pic_id){
                    unset($ids_arr[$key]);
                }
            }
            $pic_ids = implode(',',$ids_arr);
            $data = array(
                'id'=>$dyn_id,
                'pic_ids' => $pic_ids
            );
            M('Dynamic')->save($data);
        }

        //删除图片
        $map = array('id' => $pic_id);
        M('Pic')->where($map)->delete();
        //删除缩略图
        $map = array('pic_id' => $pic_id);
        M('Thumb')->where($map)->delete();
        //删除收藏记录
        M('Collect')->where($map)->delete();

        $this->success();
    }
}
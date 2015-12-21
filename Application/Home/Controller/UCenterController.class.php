<?php

namespace Home\Controller;
use Think\Controller;
use Think\Upload;
use Vendor\ThinkImage\ThinkImage;

class UCenterController extends HomeController{

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

    public function index(){
        redirect(U('album'));
    }

    //我的相册--相册列表
    public function album(){
        $user_id = session('user_auth')['uid'];
        I('uid') && $user_id = I('uid');//如果没有传用户id，则获取的是自己的资料，否则获取的是其他人的用户资料
        //获取用户的具体信息
        //获取相册信息
        $Album = M('Album');
        $map = array();
        $map['uid'] = $user_id;
        //如果查看的不是登录者的相册--则不现实私密相册
        if($user_id != session('user_auth')['uid']){
            $map['limit'] = array('neq',2);
        }
        $album_list = $Album
            ->alias('a')
            // ->join('__PIC__ p on a.cover_id = p.id','left')
            ->join('__ALBUM_TYPE__ t on a.tid=t.id')
            ->field(array('a.*','t.name as typename'))
            ->order('update_time desc')
            ->where($map)
            ->select();
        $Pic = M('Pic');
        foreach ($album_list as $key => $val) {
            //获取相册的图片数量
            $map = array();
            $map['album_id'] = $val['id'];
            $album_list[$key]['number'] = $Pic->where($map)->count();
            //如果没有设置封面，则获取相册的第一章图片为封面
            if(empty($val['cover_id'])){
                $cover_id = $Pic->where($map)->getField('id');
            }else{
                $cover_id = $val['cover_id'];
            }
            //获取封面图片的路径 ---  相册封面要足够清晰，这里不使用缩略图
            $map = array();
            $map['id'] = $cover_id;
            $album_list[$key]['cover'] = $Pic->where($map)->getField('path'); 
            //获取相册的评论数
            // 
            //  评论功能暂未实现
            // 
        }
        $this->assign('album_list',$album_list);
        $this->display();
    }

    //获取相册信息
    public function get_album_info(){
        $map = array('id' => I('id'));
        $album = M('Album')->where($map)->find();
        $this->ajaxReturn($album);
    }

    //创建相册--ajax的post提交验证
    public function create_album(){
        I('name')  || $this->error('相册名称不能为空');
        I('type')  || $this->error('相册类型不能为空');
        I('limit') || $this->error('相册权限不能为空');
        //插入的数据
        $data = array(
            'uid'         => session('user_auth')['uid'],
            'tid'         => I('type'),
            'name'        => I('name'),
            'description' => I('description'),
            'limit'       => I('limit'),
            'create_time' => NOW_TIME,
            'update_time' => NOW_TIME
        );
        if(M('Album')->add($data)){
            $this->success('创建成功！正在跳转~',U('index'));
        }else{
            $this->error('相册创建失败！');
        }
    }

    //编辑相册--ajax的post提交验证
    public function edit_album(){
        I('id') || $this->error('数据出错');
        I('name')  || $this->error('相册名称不能为空');
        I('type')  || $this->error('相册类型不能为空');
        I('limit') || $this->error('相册权限不能为空');
        //更新的数据
        $data = array(
            'id'          => I('id'),
            'uid'         => session('user_auth')['uid'],
            'tid'         => I('type'),
            'name'        => I('name'),
            'description' => I('description'),
            'limit'       => I('limit'),
            'update_time' => NOW_TIME
        );
        if(M('Album')->save($data)){
            $this->success('保存成功！正在跳转~',U('index'));
        }else{
            $this->error('相册信息未更改或保存失败！');
        }
    }

    //删除相册--ajax的post提交验证
    public function del_album(){
        $Pic = M('Pic');
        $Album = M('Album');
        $Thumb = M('Thumb');
        $Collect = M('Collect');
        $Dynamic = M('Dynamic');
        $id = I('id');
        //删除相册
        $Album->where('id='.$id)->delete();
        // 删除相册对应的所有图片
        // 1.先获取相册对应的图片信息
        $pic_list = $Pic->where('album_id='.$id)->select();
        foreach ($pic_list as $key => $val) {
            //获取对应的缩略图
            $info = $Thumb->where('pic_id='.$val['id'])->find();
            //删除图片
            unlink($info['path']);
            //删除缩略图这条记录
            $Thumb->where('pic_id='.$val['id'])->delete();
            //删除原图的记录
            unlink($val['path']);
            // 删除收藏表的有关信息
            $Collect->where('pic_id='.$val['id'])->delete();
        }
        // 2.在删除
        $pic_list = $Pic->where('album_id='.$id)->delete();
        //删除动态表的相关数据
        $Dynamic->where('album_id='.$id)->delete();
        $this->success('删除成功！');
    }

    //获取相册的图片----ajax的post提交请求
    public function get_pics(){
        $map = array('album_id' => I('id'));
        $pic_list = M('Pic')
                    ->alias('p')
                    ->join('__THUMB__ t on p.id=t.pic_id','left')
                    ->where($map)
                    ->field('p.id,t.path')
                    ->select();
        if($pic_list){
            $this->success($pic_list);
        }else{
            $this->error('图片获取失败');
        }
    }

    //设置封面
    public function set_cover(){
        $data = array('id' => I('id'), 'cover_id' => I('cover_id'));
        if(M('Album')->save($data)){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }
    }


    //上传图片--ajax的post提交上传
    public function upload_pic(){
        I('album_id') || $this->error('您还未选择相册！');
        I('img_list') || $this->error('您没有上传的图片！');
        $img_list = I('img_list');
        /**
         * 保存图片和生成缩略图
         */
        //建立数组用于保存图片---动态
        $pic_id_arr = array();
        $Pic = M('Pic');
        $Thumb = M('Thumb');
        $data = array();
        $data['album_id']    = I('album_id');
        foreach ($img_list as $key => $val) {
            $path = $val;
            $path = str_replace('/paikezone/', '', $path);
            /**
             * pathinfo($path)['filename']为图片的文件名字--时间戳+6位随机数
             * substr函数把后六位随机数裁减掉
             */
            $name = substr(pathinfo($path)['filename'],0,-6);
            $data['name'] = date('Y-m-d H:i:s',$name);
            $data['create_time'] = $name;
            //把图片路径存储在pkz_pic表中
            $data['path'] = $path;
            $pic_id = $Pic->add($data);  //返回添加的图片id

            //把图片的id保存在用于保存用户动态的数组里
            $pic_id_arr[] = $pic_id;

            //生成该图片的缩略图
            $thumb_path = get_sc($path);
            $thumb_data = array(
                'pic_id' => $pic_id,
                'path'   => $thumb_path
            );
            $Thumb->add($thumb_data);
        }
        //保存用户动态
        $data = array();
        $data['uid'] = session('user_auth')['uid'];
        $data['pic_ids'] = implode(",",$pic_id_arr);
        $data['album_id'] = I('album_id');
        $data['create_time'] = NOW_TIME;
        M('Dynamic')->add($data);
        //更新相册的更新时间
        $data = array();
        $data['id'] = I('album_id');
        $data['update_time'] = NOW_TIME;
        M('Album')->save($data);
        $this->success('上传成功！正在跳转~');
    }

    //相册详情
    public function detail($album_id=''){
        $Pic =M('Pic');
        //获取相册的id
        $album_id = I('album_id');
        /**
         * 获取相册信息
         */
        $album = M('Album')->where('id='.$album_id)->find();
        $map = array();
        $map['album_id'] = $album_id;
        $album['number'] = $Pic->where($map)->count();
        if(empty($album['cover_id'])){
            $cover_id = $Pic->where($map)->getField('id');
        }else{
            $cover_id = $album['cover_id'];
        }
        $map['id'] = $cover_id;
        $album['cover'] = $Pic->where($map)->getField('path'); 
        $this->assign('album', $album);

        /**
         * 获取相册的图片
         */
        $map = array();
        $map['album_id'] = $album_id;
        //获取相册的所有图片和缩略的
        $Pic = M('Pic');
        $Thumb = M('Thumb');
        $pic_list = $Pic
            ->alias('p')
            ->join('__THUMB__ t on p.id=t.pic_id')
            ->field(array('p.*', 't.path as thumb_path'))
            ->where($map)
            ->select();
        //判断对这些照片是否收藏
        $Collect = M('Collect');
        foreach ($pic_list as $key => $val) {
            $map =array();
            $map['uid'] = session('user_auth')['uid'];
            $map['pic_id'] = $val['id'];
            if($Collect->where($map)->find()> 0){
                $pic_list[$key]['collect'] = 1;
            }else{
                $pic_list[$key]['collect'] = 0;
            }
        }
        $this->assign('pic_list',$pic_list);
        
        /**
         * 获取相册列表
         */
        $Album = M('Album');
        $map = array();
        $map['a.uid'] = I('uid');
        $map['a.id'] =array('neq', $album_id);
        $album_list = $Album
            ->alias('a')
            ->join('__PIC__ p on a.cover_id = p.id','left')
            ->field('a.*,p.path')
            ->where($map)
            ->select();
        $Pic = M('Pic');
        foreach ($album_list as $key => $val) {
            //获取相册的图片数量
            $map = array();
            $map['album_id'] = $val['id'];
            $album_list[$key]['number'] = $Pic->where($map)->count();
            //如果没有设置封面，则获取相册的第一章图片为封面
            if(empty($val['cover_id'])){
                $cover_id = $Pic->where($map)->getField('id');
            }else{
                $cover_id = $val['cover_id'];
            }
            //获取封面图片的路径 ---  相册封面要足够清晰，这里不使用缩略图
            $map = array();
            $map['id'] = $cover_id;
            $album_list[$key]['cover'] = $Pic->where($map)->getField('path'); 
        }
        $this->assign('album_list',$album_list);
        $this->display();
    }

    /**
     * 收藏照片
     */
    public function collect_add(){
        $data = array();
        I('pic_id') && $data['pic_id'] = I('pic_id');
        $data['uid'] = session('user_auth')['uid'];
        if(M('Collect')->add($data)){
            $this->success('收藏成功');
        }else{
            $this->error('收藏失败');
        }
    }

    public function collect_cancle(){
        $map = array();
        I('pic_id') && $map['pic_id'] = I('pic_id');
        $map['uid'] = session('user_auth')['uid'];
        if(M('Collect')->where($map)->delete()){
            $this->success('取消收藏成功');
        }else{
            $this->error('取消收藏失败');
        }
    }

    //动态
    public function dynamic(){
        $user_id = session('user_auth')['uid'];
        I('uid') && $user_id = I('uid');//如果没有传用户id，则获取的是自己的资料，否则获取的是其他人的用户资料
        //获取用户的具体信息
        $map =array();
        $map['uid'] = $user_id;
        $Album = M('Album');
        $Thumb = M('Thumb');
        $Pic = M('Pic');
        $dyn_list = M('Dynamic')->where($map)->select();
        foreach ($dyn_list as $key => $val) {

            //获取相册信息
            $map1 = array();
            $map1['id'] = $val['album_id'];
            $album = $Album->where($map1)->find();
            $dyn_list[$key]['album'] = $album;

            //获取具体的图片地址--获取缩略图
            $id_list = explode(',',$val['pic_ids']);
            $pic_list = array();
            $i = 0;
            foreach ($id_list as $k => $v) {
                if($i > 9){
                    break;
                }
                $pic = array();

                //获取缩略图地址
                $map1 = array();
                $map1['pic_id'] = $v;
                $pic['thumb_path'] = $Thumb->where($map1)->getField('path');
                //获取图片的实际地址
                $map1 = array();
                $map1['id'] = $v;
                $pic['pic_path'] = $Pic->where($map1)->getField('path');

                $pic_list[] = $pic;
            }
            $dyn_list[$key]['pic_list'] = $pic_list;
            $dyn_list[$key]['count'] = count($id_list);//获取总数量
        }
        $this->assign('dyn_list', $dyn_list);
        $this->display();
    }

    //收藏
    public function collect(){
        //如果没有传用户id，则获取的是自己的资料，否则获取的是其他人的用户资料
        $user_id = session('user_auth')['uid'];
        I('uid') && $user_id = I('uid');

        //获取收藏的图片列表
        $map = array();
        $map['uid'] = $user_id;
        $collect_list = M('Collect')->where($map)->select();

        //获取收藏的图片的各种信息
        $Album = M('Album');
        $User = M('User');
        $Pic = M('Pic');
        $Thumb = M('Thumb');
        foreach ($collect_list as $key => $val) {
            //获取用户信息
            $map = array();
            $map['uid'] = $val['uid'];
            $user_info = $User->where($map)->field('username')->find();

            //获取图片的信息
            $map = array();
            $map['id'] = $val['pic_id'];
            $pic_info = $Pic->where($map)->field('album_id,path')->find();

            //通过图片信息的相册id获取相册信息
            $map = array();
            $map['id'] = $pic_info['album_id'];
            $album_info = $Album->where($map)->field('name')->find();

            //通过图片信息的（图片id）获取缩略图信息
            // $map = array();
            // $map['pic_id'] = $val['pic_id'];
            // $thumb_info = $Thumb->where($map)->field('path')->find();

            //把所有的信息整合到图片信息中
            $collect_list[$key]['username'] = $user_info['username'];
            $collect_list[$key]['pic_path'] = $pic_info['path'];
            $collect_list[$key]['album_id'] = $pic_info['album_id'];
            $collect_list[$key]['album_name'] = $album_info['name'];
            // $collect_list[$key]['thumb_path'] = $thumb_info['path'];
        }
        $this->assign('collect_list', $collect_list);
        $this->display();
    }

    //个人资料
    public function userdata(){
        //获取用户id
        // $user_id = session('user_auth')['uid']; 
        // I('user_id') && $user_id = I('user_id'); //如果没有传用户id，则获取的是自己的资料，否则获取的是其他人的用户资料
        // //获取用户的具体信息
        // $map = array();
        // $map['uid'] = $user_id;
        // $data = M('User')->where($map)->find();
        // $this->assign('data',$data);
        $this->meta_title = '用户资料';
        $this->display();
    }

    //头像上传处理函数---ajax
    public function uploadImg(){
        $upload = new Upload(C('UPLOAD_CONFIG'));   // 实例化上传类
        //头像目录地址
        $path = './Uploads/Avatar/';
        if(!$upload->upload()) {                        // 上传错误提示错误信息
            $this->ajaxReturn(array('status'=>0,'info'=>$upload->getError()));
        }else{                                          // 上传成功 获取上传文件信息
            //设置文件的完整路径，路径+文件名
            $path = $path . get_avatar_name() . '.jpg';
            $temp_size = getimagesize($path);
            if($temp_size[0] < 100 || $temp_size[1] < 100){//判断宽和高是否符合头像要求
                $this->ajaxReturn(array('status'=>0,'info'=>'图片宽或高不得小于100px！'));
            }
            $this->ajaxReturn(array('status'=>1,'path'=>$path));
        }
    }

    //裁剪并保存用户头像
    public function cropImg(){
        //图片裁剪数据
        $params = I('post.');                       //裁剪参数
        if(!isset($params) && empty($params)){
            $this->error('参数错误！');
        }

        //头像目录地址
        $path = 'Uploads/Avatar/';
        //裁减后的地址
        $crop_path = $path . session('user_auth')['username'] . '_crop.jpg';
        //最终保存的地址
        $final_path = $path . session('user_auth')['username'] . '.jpg';
        //临时图片地址-- 刚才上传的头像地址
        $pic_path = $path . get_avatar_name() . '.jpg';
        //实例化裁剪类
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        //裁剪原图得到选中区域
        $Think_img->open($pic_path)->crop($params['w'],$params['h'],$params['x'],$params['y'])->save($crop_path);
        //生成缩略图
        $Think_img->open($crop_path)->thumb(150,150, 1)->save($final_path);
        //删除上传的图片和临时目录
        unlink($crop_path);
        unlink($pic_path);
        //更新个人信息中的avatar的值为1  说明已经上传过头像
        $data = array();
        $data['uid'] = session('user_auth')['uid'];
        $data['avatar'] = 'Uploads/Avatar/' . session('user_auth')['username'] . '.jpg';
        M('User')->save($data);
        $this->success('上传头像成功');
    }

    /**
     * 用户保存资料
     */
    public function saveData(){
        $data = array();
        I('uid') && $data['uid'] = I('uid');
        I('sex') && $data['sex'] = I('sex');
        I('birthday') && $data['birthday'] = I('birthday');
        I('qq') && $data['qq'] = I('qq');
        //保存信息
        M('User')->save($data);
        $this->success('保存成功');
    }
}
<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
// use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

    public function _empty(){
        redirect('index');
    }

    //初始化
    protected function _initialize(){

        parent::_initialize();
        /**
         * 获取登录者用户的信息
         */
        $map = array();
        $map['uid'] = session('user_auth')['uid'];
        $user = M('User')->where($map)->find();
        //获取共有几个相册
        $user['album_num'] = M('Album')->where($map)->count();
        //获取共有几张照片
        $album_ids = M('Album')->where($map)->getField('id',true);
        if($album_ids){
            $map1 = array();
            $map1['album_id'] = array('in',$album_ids);
            $user['pic_num'] = M('Pic')->where($map1)->count();
        }else{
            $user['pic_num'] = 0;
        }
        
        //获取收藏
        $user['collect_num'] = M('Collect')->where($map)->count();
        $this->assign('user',$user);

        /**
         * 获取相册列表
         */
        $Album = M('Album');
        $map = array();
        $map['a.uid'] = session('user_auth')['uid'];
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
    }

	//系统首页
    public function index(){
        $User = M('User');
        $Album = M('Album');
        $Thumb = M('Thumb');
        $Pic = M('Pic');
        $dyn_list = M('Dynamic')->where($map)->order('create_time desc')->select();
        foreach ($dyn_list as $key => $val) {

            //获取用户信息
            $map1 = array();
            $map1['uid'] = $val['uid'];
            $user = $User->where($map1)->find();
            $dyn_list[$key]['user'] = $user;

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
}
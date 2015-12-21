<?php

namespace Home\Controller;
use Think\Controller;

class PlazaController extends HomeController{

    public function _initialize(){

        //执行父类的初始化函数
        parent::_initialize();
        
        //获取所有的分类
        $type_list = M('AlbumType')->select();
        $this->assign('type_list', $type_list);

        //获取当前的分类
        $now_type = I('type') ? I('type') : 1; //默认1分类
        $this->assign('now_type', $now_type);

    }

    public function index(){

        $type = I('type') ? I('type') : 1; //默认1分类

        $AlbumType = M('AlbumType');
        $Album = M('Album');
        $Pic = M('Pic');
        $Thumb = M('Thumb');
        $User = M('User');

        /**
         * 获取轮播器的照片--对应类型
         */
        $map = array('tid' => $type);
        $ids = $Album->where($map)->getField('id',true);
        $map = array('p.album_id' => array('in', $ids));
        $pic_list = $Pic
            ->alias('p')
            ->join('__ALBUM__ a on p.album_id=a.id','left')
            ->join('__USER__ m on m.uid=a.uid','left')
            ->field('p.path,p.album_id,p.create_time,a.name,m.uid,m.avatar,m.username')
            ->order('p.create_time')
            ->limit(8)
            ->where($map)
            ->select();
        $this->assign('pic_list', $pic_list);

        /**
          * 相册推荐 -- 获取分类为 $type 的相册
          */ 
        $map = array('tid' => $type);
        $album_list = $Album->where($map)->limit(100)->select();
        //获取相册封面
        foreach ($album_list as $key => $val) {
            //获取相册的照片数量 -- 并保存在album_list数组中
            $map = array('album_id' => $val['id']);
            $count = $Pic->where($map)->count();
            $album_list[$key]['count'] = $count;
        }
        //通过照片数量排序
        usort($album_list,'sortByCount');
        //获取前四个
        $list = array();
        for($i = 0; $i < 4; $i ++){
            $album = $album_list[$i];
            if($album['count'] <= 0){
                break;
            }
            //获取相册封面
            if(empty($album['cover_id'])){
                $map = array('album_id' => $album['id']);
                $cover_id = $Pic->where($map)->getField('id');
            }else{
                $cover_id = $album['cover_id'];
            }
            //获取封面图片的路径 ---  相册封面要足够清晰，这里不使用缩略图
            $map = array('id' => $cover_id);
            $album['cover'] = $Pic->where($map)->getField('path');
            //获取相册主人的具体信息
            $map = array('uid'=>$album['uid']);
            $album['avatar'] = $User->where($map)->getField('avatar');
            $album['username'] = $User->where($map)->getField('username');
            $list[] = $album; 
        }
        $album_list = $list; //把值重新赋给album_list
        $this->assign('album_list', $album_list);

        /**
         * 拍客推荐
         */
        $user_list = $User->limit(100)->select();
        //通过相册数量排序
        usort($user_list,'sortByAlbumCount');
        //获取前面七个
        $list = array();
        for($i = 0; $i < 7; $i ++){
            if(empty($user_list[$i])){
                break;
            }
            $list[] = $user_list[$i];
        }
        $user_list = $list;
        $this->assign('user_list', $user_list);

        /**
         * 瀑布流模式的图片获取
         */
        //同上面”获取轮播器的照片“一样
        // 先获取10张

        $this->display();
    }

    public function get_pics(){
        $Album = M('Album');
        $Pic = M('Pic');
        $map = array('tid' => I('type'));
        $start = I('start');
        $ids = $Album->where($map)->getField('id',true);
        $map = array('p.album_id' => array('in', $ids));
        $pic_list = $Pic
            ->alias('p')
            ->join('__ALBUM__ a on p.album_id=a.id','left')
            ->join('__USER__ m on m.uid=a.uid','left')
            ->field('p.path,p.album_id,p.create_time,a.name,m.uid,m.avatar,m.username')
            ->order('p.create_time')
            ->limit($start,5)  //一次接收五张图片
            ->where($map)
            ->select();
        //转换时间格式
        foreach ($pic_list as $key => $val) {
            $pic_list[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
        }
        if(empty($pic_list)){
            $this->error();
        }else{
            $this->success($pic_list);
        }
    }
}
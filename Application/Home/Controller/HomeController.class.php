<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    protected function _initialize(){

        //初始化判断用户是否登录
        $this->login();

        $map = array();
        $map['uid'] = session('user_auth')['uid'];
        $login_user = M('User')->where($map)->find();
        $this->assign('login_user', $login_user);
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || redirect(U('User/index'));
	}
}

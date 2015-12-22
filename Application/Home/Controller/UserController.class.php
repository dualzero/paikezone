<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {

    /* 初始化 */
    protected function _initialize(){
        if(is_login()){
            //如果不是退出登录操作
            isset($_SERVER['REQUEST_URI']) && $referer = $_SERVER['REQUEST_URI'];
            if (!preg_match('/logout/i', $referer)){
                redirect(U('Index/index',array('abc'=>$referer)));
            }
        }
    }

	/* 用户首页 */
	public function index(){
		$this->display();
	}

	/* 注册页面 */
	public function register($username = '', $password = '', $repassword = '', $email = '', $verify = ''){
		//注册用户
        $username || $this->error('用户名不能为空！');
        $password || $this->error('密码不能为空！');
        $repassword || $this->error('重复密码不能为空！');
		/* 检测验证码 */
		if(!check_verify($verify)){
			$this->error('验证码输入错误！');
		}

		/* 检测密码 */
		if($password != $repassword){
			$this->error('两次密码输入不一致！');
		}

        // 判断用户是否存在
        $User = M('User');
        $map = array('username' => $username);
        $count = $User->where($map)->count();
        if($count) $this->error('用户名已存在');

        // 判断邮箱是否存在
        $map = array('email' => $email);
        $count = $User->where($map)->count();
        if($count) $this->error('邮箱已存在');

        //注册用户信息-保存
        $data = array(
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'reg_time' => NOW_TIME,
            'reg_ip'   => get_client_ip()
        );

        if($uid = $User->add($data)){
            $this->autoLogin($uid);
            $this->success('注册成功！正在登录用户...', U('UCenter/index'));
        }else{
            $this->error('注册失败');
        }

	}

	/* 登录页面 */
	public function login($username = '', $password = '', $verify = ''){
        //空值验证
        if(!$username) $this->error('用户名不能为空！');
        if(!$password) $this->error('密码不能为空！');

        /* 检测验证码 */
        if(!check_verify($verify)){
            $this->error('验证码输入错误！');
        }

        //查找该用户名字对应的密码id、密码
        $User = M('User');
        $map = array('username'=>$username);
        $user = $User->where($map)->field('uid,password')->find();

        //判断用户名或者密码的正确性
        if(!$user) $this->error('用户不存在！');
        if($user['password'] != md5($password)){
            $this->error('密码错误');
        }

        //验证成功，登录
        $this->autoLogin($user['uid']);
        $this->success('验证成功！正在登录...',U('UCenter/index'));

	}

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function autoLogin($uid){
        $User = M('User');
        //查找用户
        $user = $User->field(true)->find($uid);

         /* 更新登录信息 */
        $data = array(
            'uid'             => $user['uid'],
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $User->save($data);

        /* 记录登录SESSION */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => $user['username'],
            'last_login_time' => $user['last_login_time'],
        );
        session('user_auth', $auth);
        // session('user_auth_sign', data_auth_sign($auth));
    }

	/* 退出登录 */
	public function logout(){
        session('user_auth', null);
        redirect(U('User/index'));
	}

	/* 验证码，用于登录和注册 */
	public function verify(){
		$verify = new \Think\Verify();
		$verify->entry(1);
	}


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
        if ( IS_POST ) {
            //获取参数
            $uid        =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error('请输入原密码');
            empty($data['password']) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！');
            }else{
                $this->error($res['info']);
            }
        }else{
            $this->display();
        }
    }

}

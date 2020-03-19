<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\facade\Session;
use think\Validate;

class Login extends Controller
{

    //登录页面
    public function index ()
    {
        return $this -> fetch ('index');
    }

    /**
     * 验证码实时校验
     *
     * @param string $code [验证码]
     *                     return JSON
     */
    public function Verification_code ()
    {

        if ( !$this -> request -> isPost () ) {
            return json (array ( 'code' => -1 , 'msg' => '请求方式错误' ));
        }

        $code    = input ('?post.code') ? input ('post.code') : '';
        $captcha = new \think\captcha\Captcha();

        if ( !$captcha -> check ($code) ) {
            return json (array ( 'code' => -1 , 'msg' => '验证码错误' ));
        }
        else {
            Session ::set ('code' , $code , 'admin');
            return json (array ( 'code' => 1 , 'msg' => '验证码正确' ));
        }
    }

    /**
     * /登录
     *
     * @param  [string] $username [用户名]
     * @param  [string] $password [密码]
     * @param  [string] $code     [验证码]
     * @param  [string] $session  [是否保持登录]
     *
     * @return [JSON]
     */
    public function login ()
    {
        if ( !$this -> request -> isPost () ) {
            return json (array ( 'code' => -1 , 'msg' => '请求方式错误' ));
        }

        $data[ 'username' ] = trim ($this -> request -> param ('username'));
        $data[ 'password' ] = trim ($this -> request -> param ('password'));
        $data[ 'code' ]     = trim ($this -> request -> param ('code'));

        $rule = [
            'username|用户名' => 'require|alphaNum' ,
            'password|密码'  => 'require|alphaNum|min:6' ,
            'code|验证码'     => 'require|alphaNum|min:5' ,
        ];

        $validate = Validate ::make ($rule);
        $result   = $validate -> check ($data);
        if ( !$result ) {
            return json (array ( 'code' => 0 , 'msg' => $validate -> getError () ));
        }

//        if ( Session ::get ('code' , 'admin') != $data[ 'code' ] ) {
//            return json (array ( 'code' => -1 , 'msg' => '验证码错误' ));
//        }

        $admin = Db ::name ('admin') -> where ([ 'name' => $data[ 'username' ] ]) -> find ();

        if ( !$admin ) {
            return json (array ( 'code' => -1 , 'msg' => '用户名不存在' ));
        }

        if ( $admin[ 'status' ] != 1 ) {
            return json (array ( 'code' => -1 , 'msg' => '该帐号被冻结，请联系管理员' ));
        }
        if ( $admin[ 'pwd' ] == md5 (md5 ($data[ 'password' ]) . $admin[ 'salt' ]) ) {
            unset($admin[ 'password' ]);
            unset($admin[ 'salt' ]);
            Session ::delete ('code' , 'admin');
            Session ::set ('admin' , $admin);
            return json (array ( 'code' => 1 , 'msg' => 'ok' ));
        }
        else {
            Session ::delete ('code' , 'admin');
            return json (array ( 'code' => -1 , 'msg' => '帐号或密码错误' ));
        }
    }

}

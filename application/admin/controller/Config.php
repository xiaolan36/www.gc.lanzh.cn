<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Session;

class Config extends Common
{

    /**
     *修改密码
     */
    public function password ()
    {
        if ( $this -> request -> isAjax () ) {
            $admin = Db ::name ('admin') -> where ('id' , Session ::get ('admin')[ 'id' ]) -> find ();
            if ( $admin[ 'pwd' ] != md5 (md5 ($this -> params[ 'password' ]) . $admin[ 'salt' ]) ) {
                $this -> return_msg (400 , '原始密码错误');
            }

            $data[ 'salt' ] = rand (0 , 9) . rand (0 , 9) . rand (0 , 9) . rand (0 , 9);
            $data[ 'pwd' ]  = md5 (md5 ($this -> params[ 'new_password' ]) . $data[ 'salt' ]);

            if ( Db ::name ('admin') -> where ('id' , $admin[ 'id' ]) -> update ($data) ) {
                $this -> return_msg (200 , '修改成功');
            }
            else {
                $this -> return_msg (400 , '修改错误');
            }
        }
        else {
            return $this -> fetch ('password' , [
                'title'  => '系统设置' ,
                'title2' => '修改密码' ,
            ]);
        }
    }

    /**
     *修改网址信息
     */
    public function website ()
    {

        if ( $this -> request -> isAjax () ) {
            foreach ( $this -> params as $key => $val ) {
                if ( empty($val) ) {
                    unset($this -> params[ $key ]);
                }
            }
            if ( Db ::name ('config') -> where ('id' , 1) -> update ($this -> params) ) {
                $this -> return_msg (200 , '修改成功');
            }
            else {
                $this -> return_msg (400 , '修改错误');
            }
        }
        else {
            $list = Db ::name ('config') -> where ('id' , 1) -> find ();
            return $this -> fetch ('website' , [
                'list'   => $list ,
                'title'  => '系统设置' ,
                'title2' => '基础配置' ,
            ]);
        }
    }

}
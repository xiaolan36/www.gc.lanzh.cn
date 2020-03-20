<?php

namespace app\admin\Controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use think\Validate;
use think\facade\Env;

/**
 *主控制器
 */
class Common extends Controller
{

    protected $request; //用来处理参数s
    protected $validate; //用来验证请求参数
    protected $params; //过滤后符合要求的参数
    protected $rules = array (

        //主页
        'Index'   => array (
            'index'          => array () ,
            'end_admin'      => array () ,
            'get_menu'       => array () ,
            'get_info_money' => array () ,
        ) ,
        //管理员管理
        'Admin'   => array (
            'index'        => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //删除用户
            'admin_del'    => array (
                'id' => 'require|number' ,
            ) ,
            //修改用户
            'admin_edit'   => array (
                'ids' => 'require|number' ,
            ) ,
            //修改用户状态
            'admin_status' => array (
                'id'     => 'require|number' ,
                'status' => 'require|number' ,
            ) ,
            //添加管理员
            'add_admin'    => array (
                'name|账号'           => 'alphaNum' ,
                'password|密码'       => 'alphaDash|min:6' ,
                'statusifyID|管理员类型' => 'number' ,
            ) ,
            'edit_admin'   => array (
                'ids' => 'require|number' ,
            ) ,
            //修改管理员
            'edit_admin_s' => array (
                'name|账号'           => 'alphaNum' ,
                'password|密码'       => 'alphaDash|min:6' ,
                'statusifyID|管理员类型' => 'number' ,
            ) ,

            //权限页面
            'role'         => array () ,
            //修改权限页面
            'edit_role'    => array (
                'ids' => 'require|number' ,
            ) ,
            //修改权限
            'edit_role_s'  => array (
                'roleid'    => 'require|number' ,
                'Character' => 'require|array' ,
            ) ,
            //删除角色
            'role_del'     => array (
                'id' => 'require|number' ,
            ) ,
            //添加角色
            'add_role'     => array (
                'rolename|角色名称'       => 'chsAlpha' ,
                'role_remarks|备注'     => 'chsAlpha' ,
                'Character|请至少选择一个权限' => 'array' ,
            ) ,

        ) ,
        //项目管理
        'Project' => array (
            //项目列表
            'index'              => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
                'p_id'        => 'number' ,
            ) ,
            //添加项目
            'add_project'        => array (
                'name|项目名称' => 'chsDash' ,
                'p_id|项目分类' => 'number' ,
            ) ,
            //修改项目昵称
            'name_edit_project'  => array (
                'name|项目名称' => 'chsDash' ,
                'id'        => 'number' ,
                'p_id|项目分类' => 'number' ,
            ) ,
            //项目分类
            'project_class'      => array (
                'statusifyID' => 'number' ,
                'start'       => 'date' ,
                'end'         => 'date' ,
                'searchInput' => 'chsDash' ,
            ) ,
            //添加分类
            'add_class_project'  => array (
                'name|分类名称' => 'chsDash' ,
            ) ,
            //修改分类昵称
            'class_edit_project' => array (
                'name|分类名称' => 'chsDash' ,
                'id'        => 'number' ,
            ) ,
            //删除分类
            'class_del_project'  => array (
                'id' => 'require|number' ,
            ) ,

            //项目流程
            'project_auth'       => array () ,
            //删除项目
            'project_del'        => array (
                'id' => 'require|number' ,
            ) ,
            'project_info'       => array (
                'ids' => 'require|number' ,
            ) ,
            'edit_project'       => array (
                'ids' => 'require|number' ,
            ) ,
            'save_project'       => array () ,

        ) ,
        'Config'  => array (
            'password' => array (
                'password|原始密码'          => 'chsDash' ,
                'new_password|新密码'       => 'chsDash' ,
                'new_password_true|两次密码' => 'confirm:new_password' ,
            ) ,
            'website'  => array (
                'info_one|系统名称'   => 'chsDash' ,
                'info_two|版权信息'   => 'chsDash' ,
                'info_three|技术支持' => 'chsDash' ,
                'info_four|技术电话'  => 'mobile' ,
            )
        )
    );

    //初始化
    protected function initialize ()
    {
        parent ::initialize ();
        if ( !Session ::has ('admin') ) {
            $this -> success ('请先登录' , 'Login/admin_niu');
        }

        //路由参数校验
        $this -> request = Request ::instance ();
        $this -> params  = $this -> check_params ($this -> request -> param ());

        //获取菜单
        $adminid   = Session ::get ('admin')[ 'id' ];
        $menu_list = db () -> query ("SELECT m.* FROM `api_menu` as m ,`api_admin` as u, `api_admin_role` as ur ,`api_menu_role` as rm WHERE u.role=ur.roleid and ur.roleid=rm.r_id AND m.id=rm.m_id AND u.id={$adminid}");
        $this -> assign ('menu_list' , $menu_list);
        //获取系统信息
        $config = Db ::name ('config') -> where ('id' , 1) -> field ('info_one') -> find ();
        $this -> assign ('config' , $config);

        //权限校验
        if ( !$this -> auth ($menu_list) ) {
            $this -> success ('权限不足' , 'Index/index');
        }

    }

    /**
     *权限验证
     *
     * @param $list
     * User: lanzh
     * Date: 2020/3/3 16:10
     */
    public function auth ( $list )
    {
        if ( strtoupper ($this -> request -> controller ()) == 'INDEX' ) {
            return true;
        }

        foreach ( $list as $key => $val ) {
            if ( $val[ 'parentid' ] != 0 ) {
                $url = strtoupper ($val[ 'url' ]);
                $M   = $this -> cut_str ($url , '/' , 0);
                $C   = $this -> cut_str ($url , '/' , 1);
//                $A   = $this -> cut_str ($url , '/' , 2);$A == strtoupper ($this -> request -> action ()
                if ( $M == strtoupper ($this -> request -> module ()) && $C == strtoupper ($this -> request -> controller ()) ) {
                    return true;
                }
            }
        }
#
        return false;
    }

    /**
     * 按符号截取字符串的指定部分
     *
     * @param string $str    需要截取的字符串
     * @param string $sign   需要截取的符号
     * @param int    $number 如是正数以0为起点从左向右截  负数则从右向左截
     *
     * @return string 返回截取的内容
     */
    public function cut_str ( $str , $sign , $number )
    {
        $array  = explode ($sign , $str);
        $length = count ($array);
        if ( $number < 0 ) {
            $new_array  = array_reverse ($array);
            $abs_number = abs ($number);
            if ( $abs_number > $length ) {
                return 'error';
            }
            else {
                return $new_array[ $abs_number - 1 ];
            }
        }
        else {
            if ( $number >= $length ) {
                return 'error';
            }
            else {
                return $array[ $number ];
            }
        }
    }

    /**
     * 验证器
     *
     * @param  [ary] $arr [传递过来的参数]
     *
     * @return [ary]      [返回验证通过的参数]
     */
    public function check_params ( $arr )
    {
        //获取参数的验证规则
        $rule = $this -> rules[ $this -> request -> controller () ][ $this -> request -> action () ];
        //验证参数并返回错误
        $this -> validate = Validate ::make ($rule);
        foreach ( $arr as $key => $val ) {
            if ( !is_array ($arr[ $key ]) ) {
                $arr[ $key ] = trim ($val);
            }
        }

        if ( !$this -> validate -> check ($arr) ) {
            $this -> return_msg (400 , $this -> validate -> getError ());
        }
        //通过验证返回
        return $arr;

    }

    /**
     * /封装json输出
     *
     * @param  [intval] $code [状态码]
     * @param  [string] $msg  [错误详情]
     * @param array $data [返回的数据]
     *
     * @return [json]       [description]
     */
    public function return_msg ( $code , $msg , $data = [] )
    {

        $return_data[ 'code' ] = $code;
        $return_data[ 'msg' ]  = $msg;
        if ( !empty($data) ) {
            $return_data[ 'data' ] = $data;
        }
        echo json_encode ($return_data);
        exit;
    }

}

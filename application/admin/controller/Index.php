<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Session;

class Index extends Common
{

    //后台主页
    public function index ()
    {

        $version  = Db ::query ('SELECT VERSION() AS ver');
        $mysql_id = Db ::query ('SELECT VERSION() from dual limit 1');
        $config   = Db ::name ('config') -> where ('id' , 1) -> find ();
        $info     = array (
            '版权信息'                 => $config[ 'info_two' ] ,
            '技术支持'                 => $config[ 'info_three' ] ,
            '技术电话'                 => $config[ 'info_four' ] ,
            '登录ip/所在地'             => $_SERVER[ "REMOTE_ADDR" ] . '[' . $this -> ip_address ($_SERVER[ "REMOTE_ADDR" ]) . ']' ,
            '操作系统'                 => PHP_OS ,
            '运行环境'                 => $_SERVER[ "SERVER_SOFTWARE" ] ,
            'PHP运行方式'              => php_sapi_name () ,
            '运行PHP版本'              => PHP_VERSION ,
            'MySQL数据库版本'           => $mysql_id[ 0 ][ 'VERSION()' ] ,
            '网站目录'                 => $_SERVER[ 'DOCUMENT_ROOT' ] ,
            '服务器端口'                => $_SERVER[ 'SERVER_PORT' ] ,
            '上传附件限制'               => ini_get ('upload_max_filesize') ,
            '执行时间限制'               => ini_get ('max_execution_time') . '秒' ,
            '服务器时间'                => date ("Y年n月j日 H:i:s") ,
            '北京时间'                 => gmdate ("Y年n月j日 H:i:s" , time () + 8 * 3600) ,
            '服务器域名/IP'             => $_SERVER[ 'SERVER_NAME' ] . ' [ ' . gethostbyname ($_SERVER[ 'SERVER_NAME' ]) . ' ]' ,
            '剩余空间'                 => round (( disk_free_space (".") / ( 1024 * 1024 ) ) , 2) . 'M' ,
            'register_globals'     => get_cfg_var ("register_globals") == "1" ? "ON" : "OFF" ,
            'magic_quotes_gpc'     => ( 1 === get_magic_quotes_gpc () ) ? 'YES' : 'NO' ,
            'magic_quotes_runtime' => ( 1 === get_magic_quotes_runtime () ) ? 'YES' : 'NO' ,
        );

        $this -> assign ('info' , $info);
        return $this -> fetch ('index' , [ 'title' => '首页' , 'title2' => '首页' ]);
    }

    /*
     * 淘宝ip地址查询接口
     */
    public function ip_address ( $ip )
    {

        $durl = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;

        // 初始化

        $curl = curl_init ();

        // 设置url路径

        curl_setopt ($curl , CURLOPT_URL , $durl);

        // 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。

        curl_setopt ($curl , CURLOPT_RETURNTRANSFER , true);

        // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回

        curl_setopt ($curl , CURLOPT_BINARYTRANSFER , true);

        // 执行

        $data = json_decode (curl_exec ($curl));

        // 关闭连接

        curl_close ($curl);
        // 返回数据
        if ( $data ) {
            return $data -> data -> country . $data -> data -> region . $data -> data -> city . $data -> data -> isp;
        }
        else {
            return '请刷新页面重试';
        }
    }

    //退出登录
    public function end_admin ()
    {
        Session ::delete ('admin');
        $this -> success ('已退出' , 'login/admin_niu');
    }

    //获取菜单
    public function get_menu ()
    {

        $adminid = Session ::get ('admin')[ 'role' ];

        $menu_list = db () -> query ("SELECT m.* FROM api_menu as m ,api_admin as u, api_admin_role as ur ,api_menu_role as rm WHERE u.role=ur.roleid and ur.roleid=rm.r_id AND m.id=rm.m_id AND u.id={$adminid}");

        if ( $menu_list ) {
            die(json_encode (array (
                'code'     => 1 ,
                'msg'      => '获取菜单ok' ,
                'menulist' => $menu_list
            )));
        }
        else {
            die(json_encode (array (
                'code' => -1 ,
                'msg'  => '失败'
            )));
        }
    }

}

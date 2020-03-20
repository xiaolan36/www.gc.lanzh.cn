<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
Route ::get ('/' , 'admin/login/index');//后台登录
Route ::get ('admin_niu' , 'admin/login/index');//后台登录
//后台主页
Route ::group ('admin/index' , function () {
    Route ::get ('end_admin' , 'admin/index/end_admin');
    Route ::get ('index' , 'admin/index/index');
    Route ::post ('get_menu' , 'admin/index/get_menu');
});

//后台登录路由
Route ::group ('admin/login' , function () {
    Route ::get ('admin_niu' , 'admin/login/index');
    Route ::post ('verification_code' , 'admin/login/verification_code');
    Route ::post ('login' , 'admin/login/login');
});

//管理员
Route ::group ('admin/admin' , function () {
    Route ::get ('index' , 'admin/admin/index');
    Route ::rule ('add_admin' , 'admin/admin/add_admin' , 'GET|POST');
    Route ::get ('edit_admin' , 'admin/admin/edit_admin');
    Route ::post ('edit_admin_s' , 'admin/admin/edit_admin_s');
    Route ::post ('admin_status' , 'admin/admin/admin_status');
    Route ::post ('admin_del' , 'admin/admin/admin_del');

    Route ::get ('role' , 'admin/admin/role');
    Route ::rule ('add_role' , 'admin/admin/add_role' , 'GET|POST');
    Route ::post ('role_del' , 'admin/admin/role_del');
    Route ::get ('edit_role' , 'admin/admin/edit_role');
    Route ::post ('edit_role_s' , 'admin/admin/edit_role_s');
});

//项目
Route ::group ('admin/project' , function () {
    Route ::get ('index' , 'admin/project/index');
    Route ::rule ('add_project' , 'admin/project/add_project' , 'GET|POST');
    Route ::rule ('project_auth' , 'admin/project/project_auth' , 'GET|POST');
    Route ::rule ('name_edit_project' , 'admin/project/name_edit_project' , 'GET|POST');
    Route ::post ('project_project' , 'admin/project/project_del');
    Route ::get ('project_info' , 'admin/project/project_info');
    Route ::get ('edit_project' , 'admin/project/edit_project');
    Route ::post ('save_project' , 'admin/project/save_project');
    Route ::get ('project_class' , 'admin/project/project_class');

});

Route ::group ('admin/config' , function () {
    Route ::rule ('password' , 'admin/config/password' , 'GET|POST');
    Route ::rule ('website' , 'admin/config/website' , 'GET|POST');

});



<?php

namespace app\admin\controller;

use think\Db;
use think\Exception;
use think\facade\Session;
use think\facade\Env;

header ("Content-type: text/html; charset=utf-8");

class Project extends Common
{

    /**
     *项目列表主页
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function index ()
    {
        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //分类不为空
            if ( isset($param[ 'p_id' ]) && !empty($param[ 'p_id' ]) ) {
                $map[] = [ 'p_id' , 'eq' , $param[ 'p_id' ] ];
            }

            //状态不为空
            if ( isset($param[ 'statusifyID' ]) && !empty($param[ 'statusifyID' ]) ) {
                $map[] = [ 'status' , 'eq' , $param[ 'statusifyID' ] ];
            }

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                $map[] = [ 'name' , 'like' , "%{$param[ 'searchInput' ]}%" ];
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'addtime' , 'between' , "$a,$b" ];
                }
            }
        }

        $list = Db ::name ('project') -> where ($map) -> order ('id' , 'asc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {
            $num = Db ::name ('project_info') -> where (array (
                'p_id'        => $item[ 'id' ] ,
                'status_true' => 2
            )) -> count ();
            if ( $num == 9 ) {
                $item[ 'status' ] = 2;
            }
            else {
                $item[ 'speed' ] = ( $num * 10 ) . '%';
            }
            $item[ 'p_id' ] = Db ::name ('project_class') -> where ('id' , $item[ 'p_id' ]) -> value ('name');
            return $item;
        });

        $menu = Db ::name ('project_class') -> select ();
        return $this -> fetch ('index' , [
            'list'   => $list ,
            'menu'   => $menu ,
            'title'  => '项目管理' ,
            'title2' => '项目列表' ,
        ]);
    }

    /**
     *项目分类
     * User: lanzh
     * Date: 2020/2/20 17:47
     */
    public function project_class ()
    {
        $param = $this -> params;
        $map   = [];
        if ( !empty($param) ) {

            //搜索内容不为空
            if ( isset($param[ 'searchInput' ]) && !empty($param[ 'searchInput' ]) ) {
                $map[] = [ 'name' , 'like' , "%{$param[ 'searchInput' ]}%" ];
            }

            //时间戳不为空
            if ( isset($param[ 'start' ]) && isset($param[ 'end' ]) && !empty($param[ 'start' ]) && !empty($param[ 'end' ]) ) {
                $a     = strtotime ($param[ 'start' ]);
                $b     = strtotime ($param[ 'end' ]) + 86400;
                $map[] = [ 'addtime' , 'between' , "$a,$b" ];
            }
            else {
                if ( isset($param[ 'start' ]) && !empty($param[ 'start' ]) ) {
                    $a     = strtotime ($param[ 'start' ]);
                    $b     = strtotime ($param[ 'start' ]) + 86400;
                    $map[] = [ 'addtime' , 'between' , "$a,$b" ];
                }
            }
        }

        $list = Db ::name ('project_class') -> where ($map) -> order ('id' , 'asc') -> paginate (20 , false , [ 'query' => request () -> param () ]) -> each (function ( $item , $key ) {

            $item[ 'pid' ] = Db ::name ('project') -> where ('p_id' , $item[ 'id' ]) -> count ();

            return $item;
        });
        return $this -> fetch ('project_class' , [
            'list'   => $list ,
            'title'  => '项目管理' ,
            'title2' => '分类管理' ,
        ]);
    }

    /**
     *添加分类
     * User: lanzh
     * Date: 2020/3/3 14:55
     */
    public function add_class_project ()
    {

        if ( $this -> request -> isAjax () ) {

            if ( Db ::name ('project_class') -> where ('name' , $this -> params[ 'name' ]) -> find () ) {
                $this -> return_msg (400 , '该项目已存在');
            }

            $data[ 'name' ]    = $this -> params[ 'name' ];
            $data[ 'addtime' ] = time ();

            $result = Db ::name ('project_class') -> insert ($data);
            if ( $result ) {
                $this -> return_msg (200 , '添加成功');
            }
            else {
                $this -> result (400 , '添加失败');
            }
        }
        else {

            return $this -> fetch ('add_class_project' , [
                'title'  => '项目管理' ,
                'title2' => '分类管理' ,
            ]);
        }
    }

    /**
     *修改分类
     * User: lanzh
     * Date: 2020/3/3 14:55
     */
    public function class_edit_project ()
    {

        if ( $this -> request -> isAjax () ) {

            if ( Db ::name ('project_class') -> where ('name' , $this -> params[ 'name' ]) -> find () ) {
                $this -> return_msg (400 , '该项目已存在');
            }

            if ( Db ::name ('project_class') -> where ('id' , $this -> params[ 'id' ]) -> update (array ( 'name' => $this -> params[ 'name' ] )) ) {
                $this -> return_msg (200 , '修改成功');
            }
            else {
                $this -> return_msg (400 , '修改失败');
            }

        }
        else {

            $param = $this -> params;
            $list  = Db ::name ('project_class') -> where ('id' , $param[ 'ids' ]) -> find ();
            return $this -> fetch ('class_edit_project' , [
                'list'   => $list ,
                'title'  => '项目管理' ,
                'title2' => '分类管理' ,
            ]);
        }
    }

    /**
     * 删除分类
     *
     * @param int $[id] [<项目 id>]
     *
     * @return [json] [删除结果]
     */
    public function class_del_project ()
    {
        $param = $this -> params;
        $num   = Db ::name ('project') -> where ('p_id' , $param[ 'id' ]) -> count ();
        if ( $num > 0 ) {
            $this -> return_msg (400 , '删除失败,该分类下已有项目无法删除');
        }
        else {
            if ( Db ::name ('project_class') -> delete ($param[ 'id' ]) ) {
                $this -> return_msg (200 , '已删除');
            }
            else {
                $this -> return_msg (200 , '删除失败');
            }
        }
    }

    /**
     *添加项目
     * User: lanzh
     * Date: 2020/3/3 14:55
     */
    public function add_project ()
    {

        if ( $this -> request -> isAjax () ) {
            if ( Db ::name ('project') -> where ('name' , $this -> params[ 'name' ]) -> find () ) {
                $this -> return_msg (400 , '该项目已存在');
            }
            $data[ 'name' ]    = $this -> params[ 'name' ];
            $data[ 'speed' ]   = '暂无进度';
            $data[ 'price' ]   = 0;
            $data[ 'status' ]  = 1;
            $data[ 'addtime' ] = time ();
            $data[ 'p_id' ]    = $this -> params[ 'p_id' ];;

            Db ::startTrans ();

            try {
                $id = Db ::name ('project') -> insertGetId ($data);
                unset($data);
                $info = Db ::name ('project_menu') -> order ('id' , 'asc') -> select ();
                foreach ( $info as $key => $val ) {
                    $data[] = array (
                        'name'        => $val[ 'name' ] ,
                        'type'        => $val[ 'type_id' ] ,
                        'p_id'        => $id ,
                        'price'       => 0 ,
                        'speed'       => '未开始' ,
                        'status'      => '3' ,
                        'status_true' => 1 ,
                    );
                }

                Db ::name ('project_info') -> insertAll ($data);
                Db ::commit ();
                $this -> return_msg (200 , '添加成功');

            } catch ( Exception $exception ) {

                Db ::rollback ();
                $this -> result (400 , '添加失败');
            }
        }
        else {
            $list = Db ::name ('project_class') -> select ();
            return $this -> fetch ('add_project' , [
                'list'   => $list ,
                'title'  => '项目管理' ,
                'title2' => '项目列表' ,
            ]);
        }
    }

    /**
     *修改项目
     * User: lanzh
     * Date: 2020/3/3 14:55
     */
    public function name_edit_project ()
    {

        if ( $this -> request -> isAjax () ) {

            if (
            Db ::name ('project') -> where ('id' , $this -> params[ 'id' ]) -> update (array (
                'name' => $this -> params[ 'name' ] ,
                'p_id' => $this -> params[ 'p_id' ]
            ))
            ) {
                $this -> return_msg (200 , '修改成功');
            }
            else {
                $this -> return_msg (400 , '修改失败');
            }

        }
        else {

            $param = $this -> params;
            $menu  = Db ::name ('project_class') -> select ();
            $list  = Db ::name ('project') -> where ('id' , $param[ 'ids' ]) -> find ();
            return $this -> fetch ('name_edit_project' , [
                'list'   => $list ,
                'menu'   => $menu ,
                'title'  => '项目管理' ,
                'title2' => '项目列表' ,
            ]);
        }
    }

    /**
     * 项目权限
     */
    public function project_auth ()
    {
        if ( $this -> request -> isAjax () ) {
            $param = $this -> params;
            foreach ( $param as $key => $val ) {
                foreach ( $val as $k => $v ) {
                    $data[] = array ( 'a_id' => $v , 'u_id' => substr ($key , -1) );
                }
            }

            Db ::startTrans ();

            try {
                Db ::name ('project_auth') -> where ('a_id' , '<>' , 1) -> delete ();
                Db ::name ('project_auth') -> insertAll ($data);
                Db ::commit ();
                $this -> return_msg (200 , '设置成功');
            } catch ( Exception $exception ) {
                Db ::rollback ();
                $this -> return_msg (400 , '设置失败');
            }
        }
        else {

            $admin = Db ::name ('admin') -> where ('id' , 'neq' , 1) -> field ('id,name') -> select ();
            $menu  = Db ::name ('project_menu') -> order ('id' , 'asc') -> select ();

            foreach ( $admin as $key => $val ) {
                $res = Db ::name ('project_auth') -> where ('a_id' , $val[ 'id' ]) -> field ('u_id') -> select ();

                if ( !empty($res) ) {
                    foreach ( $res as $k => $v ) {
                        $data[] = $v[ 'u_id' ];
                    }
                    $admin[ $key ][ 'auth' ] = $data;
                    unset($data);
                }
                else {
                    $admin[ $key ][ 'auth' ] = [];
                }
            }

            return $this -> fetch ('project_auth' , [
                'list'   => $admin ,
                'menu'   => $menu ,
                'title'  => '项目管理' ,
                'title2' => '项目流程' ,
            ]);
        }
    }

    /**
     * 删除
     *
     * @param int $[id] [<项目 id>]
     *
     * @return [json] [删除结果]
     */
    public function project_del ()
    {
        $param = $this -> params;
        try {
            Db ::name ('project') -> delete ($param[ 'id' ]);
            Db ::name ('project_info') -> where ('p_id' , $param[ 'id' ]) -> delete ();
            $this -> return_msg (200 , '已删除');
        } catch ( Exception $exception ) {
            Db ::rollback ();
            $this -> return_msg (400 , '删除失败');
        }
    }

    /**
     *项目详情
     * User: lanzh
     * Date: 2020/3/3 15:10
     */
    public function project_info ()
    {
        $list = Db ::name ('project_info') -> alias ('a') -> join ('api_project b ' , 'a.p_id=b.id') -> where ('a.p_id' , $this -> params[ 'ids' ]) -> field ('a.* ,b.name as projectname') -> select ();

        $auth = $this -> admin_auth ();

        $sum = 0;
        foreach ( $list as $key => $val ) {

            //获取可编辑的任务
            if ( !in_array ($val[ 'type' ] , $auth) ) {
                unset($list[ $key ]);
                continue;
            }

            $list[ $key ][ 'userid' ]          = empty($list[ $key ][ 'userid' ]) ? '未操作' : Db ::name ('admin') -> field ('name') -> where ('id' , $list[ $key ][ 'userid' ]) -> find ()[ 'name' ];
            $list[ $key ][ 'plan_start_time' ] = empty($list[ $key ][ 'plan_start_time' ]) ? '未开始' : date ('Y-m-d H:i' , $list[ $key ][ 'plan_start_time' ]);
            $list[ $key ][ 'plan_end_time' ]   = empty($list[ $key ][ 'plan_end_time' ]) ? '未开始' : date ('Y-m-d H:i' , $list[ $key ][ 'plan_end_time' ]);
            $list[ $key ][ 'start_time' ]      = empty($list[ $key ][ 'start_time' ]) ? '未开始' : date ('Y-m-d H:i' , $list[ $key ][ 'start_time' ]);
            $list[ $key ][ 'end_time' ]        = empty($list[ $key ][ 'end_time' ]) ? '未开始' : date ('Y-m-d H:i' , $list[ $key ][ 'end_time' ]);
            $list[ $key ][ 'speed' ]           = empty($list[ $key ][ 'speed' ]) ? '未开始' : $list[ $key ][ 'speed' ] . '%';
            $sum                               += $list[ $key ][ 'price' ];
        }
        return $this -> fetch ('project_info' , [
            'list'   => $list ,
            'sum'    => $sum ,
            'role'=>Session::get ('admin')['role'],
            'title'  => '项目管理' ,
            'title2' => '项目列表' ,
        ]);
    }

    /**
     * 编辑任务
     */
    public function edit_project ()
    {
        $id   = $this -> params[ 'ids' ];
        $pid  = $this -> params[ 'pid' ];
        $type = $this -> params[ 'type' ];
        $auth = $this -> admin_auth ();

        if ( !in_array ($type , $auth) ) {
            $this -> error ('你没有权限');
        }

        $res = Db ::name ('project_info') -> where ('p_id' , $pid) -> min ('id');
        if ( $id != $res ) {

            $res = Db ::name ('project_info') -> where ('id' , $id - 1) -> field ('status_true') -> find ();
            if ( $res[ 'status_true' ] != 2 ) {
                $this -> error ('上个项目流程未提交，无法编辑');
            }
        }

        $list = Db ::name ('project_info') -> where ('id' , $id) -> find ();
        if ( isset($this -> params[ 'status' ]) && !empty($this -> params[ 'status' ]) ) {
            $list[ 'hide' ] = '1';
        }
        if ( !empty($list[ 'uploads' ]) ) {
            $list[ 'uploads' ] = explode (',' , $list[ 'uploads' ]);
            array_pop ($list[ 'uploads' ]);
        }

        return $this -> fetch ('edit_project' , [
            'list'   => $list ,
            'title'  => '项目管理' ,
            'title2' => '项目列表' ,
        ]);
    }

    /**
     * 保存任务
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function save_project ()
    {
        // 获取表单上传文件
        $files = request () -> file ('uploads');
        if ( !empty($files) ) {
            $str = '';
            foreach ( $files as $file ) {
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file -> validate ([
                    'size' => 1024 * 1024 * 5 ,
                ]) -> move (Env ::get ('root_path') . 'public' . DIRECTORY_SEPARATOR . 'uploads' , '');
                if ( $info ) {
                    $filename  = $info -> getSaveName ();
                    $exclePath = iconv ("GB2312" , "UTF-8" , $filename);
                    $str       .= $exclePath . ',';
                }
                else {
                    $this -> return_msg (400 , $file -> getError ());
                }
            }

            $this -> params[ 'uploads' ] = $str;

        }
        else {
            unset($this -> params[ 'uploads' ]);
        }
        if ( isset($this -> params[ 'yes' ]) ) {
            unset($this -> params[ 'yes' ]);
            $this -> params[ 'status_true' ] = 2;
            $this -> params[ 'status' ]      = 1;
            $this -> params[ 'userid' ]      = Session ::get ('admin')[ 'id' ];
            $this -> params[ 'username' ]    = Session ::get ('admin')[ 'name' ];
        }
        $this -> params[ 'plan_start_time' ] = strtotime ($this -> params[ 'plan_start_time' ]);
        $this -> params[ 'plan_end_time' ]   = strtotime ($this -> params[ 'plan_end_time' ]);
        $this -> params[ 'start_time' ]      = strtotime ($this -> params[ 'start_time' ]);
        $this -> params[ 'end_time' ]        = strtotime ($this -> params[ 'end_time' ]);
        if ( Db ::name ('project_info') -> update ($this -> params) ) {
            $this -> return_msg (200 , '提交成功');
        }
        else {
            $this -> return_msg (400 , '提交失败');
        }
    }

    /**
     * 鉴权
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function admin_auth ()
    {
        $adminid = Session ::get ('admin');
        $auth    = Db ::name ('project_auth') -> where ('a_id' , $adminid[ 'id' ]) -> select ();
        foreach ( $auth as $key => $val ) {
            $data[] = $val[ 'u_id' ];
        }
        if ( empty($data) ) {
            $this -> error ('你没有权限');
        }
        return $data;
    }
}
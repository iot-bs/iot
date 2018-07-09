<?php
namespace app\admin\controller;
//use Think\Controller;
class Privilege extends Base
{

    /**
     * 首页渲染
     */
	public function index() {
        $list = $this->privilege->pritree();
//        var_dump(gettype($list));exit;
        $this->assign(array(
            'title' => '权限列表',
            'list' => $list
            ));
        return $this->fetch();
	}
	/**
     * 管理员权限添加页面渲染
     */
    public function add() {
        header("Content-Type: text/html; charset=utf-8");
        $pris = $this->privilege->pritree();
        $this->assign(array(
            'pris' => $pris,
            'title' => '添加权限'
            ));
        return $this->fetch();
    }
    /**
     * 管理员权限添加
     */
    public function do_add() {
        if(request()->isPost()){
            $data = array(
                'pri_name' => input('pri_name'),
                'mname' => input('post.mname'),
                'cname' => input('post.cname'),
                'aname' => input('post.aname'),
                'parent_id' => input('post.parent_id')
                );
            $pri = $this->privilege->save($data);
                if($pri){
                    $return = array('msg' => '添加成功', 'status' => 0);
                }else{
                    $return = array('msg' => '添加成功', 'status' => 1);
                }
                return json($return);
        }
    }
    /**
     * 管理员权限页面渲染
     */
    public function edit($id) {
        $prires=$this->privilege->get($id);
        $pris=$this->privilege->pritree();
        $this->assign(array(
            'title' => '修改权限',
            'pris' => $pris,
            'prires' => $prires
            ));
        return $this->fetch();
    }
    /**
     * 管理员权限修改
     */
    public function do_edit() {
        if(request()->isPost()){
            $data = array(
                'pri_name' => input('post.pri_name'),
                'mname' => input('post.mname'),
                'cname' => input('post.cname'),
                'aname' => input('post.aname'),
                'parent_id' => input('post.parent_id')
                );
                if($this->privilege->save($data,['id' => input('post.id')])){
                    $return = array('msg' => '修改成功', 'status' => 0);
                }else{
                    $return = array('msg' => '修改失败', 'status' => 1);
                }
            return json($return);
        }
    }
    /**
     * 管理员权限删除
     */
    public function del(){
        if (request()->isGet()) {
            $id = input('get.id');
            if($this->privilege->destroy($id)){
                return json(array('msg' => '删除成功！', 'status' => 0));
            }else{
                return json(array('msg' => '删除失败！', 'status' => 1));
            }
        }
    }

}





?>
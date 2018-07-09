<?php
namespace app\admin\controller;

class Role extends Base
{

	public function index() {
        $list = $this->role->getAllRoles();
//        var_dump($list);exit;
        $this->assign([
        	'title' => '角色列表',
            'list' => $list,
            ]);// 赋值数据集
		return $this->fetch();
	}
	/**
	 * 添加管理员角色
	 */
	public function add(){
		$listpri=$this->privilege->pritree();
		$this->assign([
			'title' => '添加角色',
			'listpri' => $listpri,
            'so' => '',
			]);
		return $this->fetch();
	}
	public function do_add(){
		if (request()->isPost()) {
		    $input = input('post.');
			$data = array(
				'rolename' => $input['rolename'],
				'description' => $input['description'],
				'pri_id_list' => implode(',', $input['pri_id_list'])
				);
			if (empty($data['pri_id_list'])) {
				$return = array('msg' => '权限不得为空', 'status' => 1);
			}else {
					if ($this->role->save($data)) {
						$return = array('msg' => '添加成功', 'status' => 0);
					}else{
						$return = array('msg' => '添加失败', 'status' => 1);
					}
			}
			return json($return);
		}
	}
	/**
	 * 修改管理员角色
	 */
	public function edit($id){
		$roleres = $this->role->get($id);
		$listpri = $this->privilege->pritree();
		$this->assign(array(
			'title' => '编辑角色',
			'listpri' => $listpri,
		    'roleres' => $roleres
		    ));
		return $this->fetch();
	}
	/**
	 * 修改管理员角色
	 */
	public function do_edit(){
		if (request()->isPost()) {
		    $input = input('post.');
			$data = array(
				'rolename' => $input['rolename'],
				'description' => $input['description'],
				'pri_id_list' => implode(',', $input['pri_id_list'])
				);
			if (empty($data['pri_id_list'])) {
				$return = array('msg' => '权限不得为空', 'status' => 1);
			}else {
					if ($this->role->save($data,['id' => input('post.id')])) {
						$return = array('msg' => '修改成功', 'status' => 0);
					}else{
						$return = array('msg' => '修改失败', 'status' => 1);
					}
		    }
		    return json($return);
		}
	}
    /**
     * 删除管理员
     */
    public function del(){
        if (request()->isGet()) {
            $id = input('get.id');
            if ($id ==1) {
                return json(array('msg' => '超级管理员不能删除！', 'status' => 1));
            }
            if($this->role->destroy($id))
            {
                return json(array('msg' => '删除成功！', 'status' => 0));
            }else{
                return json(array('msg' => '删除失败！', 'status' => 1));
            }
        }
    }
}



?>
<?php
namespace app\admin\controller;
class Admin extends Base
{
    public $admin;
    public $role;
    public function initialize()
    {
        $this->admin = model('Admin');
        $this->role = model('Role');
    }

    /**
	 * 管理员列表
	 */
	public function index() {
		header("Content-Type: text/html; charset=utf-8");
		$so = input('get.so');
		$admin = $this->admin->get($this->admin_uid);
		if ($admin['role_id'] == 1) {
		    $where = [];
		    $whereor = [];
			if ($so) {
                $where[] = [
                    'adminuser', 'like', "%".$so."%"
                ];
                $whereor[] = [
                    'mobile','like','%'.$so.'%'
                ];
			}
			$list = $this->admin
				->where($where)
                ->whereOr($whereor)
				->order('id asc')
				->paginate();
		} else {
			$list = $this->admin
				->where(array('id' => $this->admin_uid))
				->order('id asc')->paginate();
		}
//		 var_dump($list);exit;
		$this->assign(array(
			'list' => $list,
			'title' => '管理员列表',
			'so' => input('get.so'),
		));
		return $this->fetch();
	}
    /**
     * 添加管理员
     */
	public function add() {

        $roles = $this->role->getRoles();
		$this->assign(array(
			'title' => '添加管理员',
			'roles' => $roles,
		));
		return $this->fetch();
	}
	// 执行添加
	public function do_add() {
		/**
		 * 添加管理数据处理
		 */
		$adinfo = $this->admin->where('name',input('post.name'))->find();
		$redata['status'] = 0;
		if (input('post.pass') != input('post.pass1')) {
			#确定密码
			$redata = array(
				'status' => 1,
				'msg' => '两次密码输入密码不相符!',
			);
		} elseif ($adinfo) {
			$redata = array(
				'status' => 1,
				'msg' => '改用户名已存在，不能重复添加!',
			);
		}
		$rand = MD5(rand(000000, 99999));
		$data = array(
			'name' => input('post.name'),
			'adminuser' => input('post.adminuser'),
			'password' => MD5(MD5(input('post.pass1') . 'QianWen') . $rand),
			'regtime' => time(),
			'mobile' => input('post.mobile'),
			'role_id' => input('post.role_id'),
			'rands' => $rand,
		);
//		print_r($data);exit;
		if ($redata['status'] !== 1) {
			$result = $this->admin->save($data);
			if ($result) {
				$redata = array(
					'status' => 0,
					'msg' => '添加成功',
				);
			} else {
				$redata = array(
					'status' => 1,
					'msg' => '添加失败',
				);
			}
		}
		return json($redata);
	}

	// 修改密码渲染模板
	public function edit_pass($id) {
		$this->assign(array(
			'title' => '修改密码',
			'id' => $id,
		));
		return $this->fetch();
	}
	// 执行修改密码
	public function do_edit_pass() {
		/**
		 *  验证当前密码
		 * */
		if (request()->isPost()) {
			$where['id'] = input('post.id');
			$info = $this->admin->where($where)->find();
			if ($info) {
				$password = input('post.password');
				$pass = input('pass');
				$pass1 = input('pass1');
				if ($info['password'] == MD5(MD5($password . 'QianWen') . $info['rands'])) {
					if ($pass == $pass1 && $pass1 != NULL) {
						$rand = MD5(rand(000000, 99999));
						$data['password'] = MD5(MD5($pass1 . 'QianWen') . $rand);
						$data['rands'] = $rand;
						$res = $this->admin->save($data,['id' => $where['id']]);
						if ($res) {
							$return = array('msg' => '修改成功', 'status' => 0);
						} else {
							$return = array('msg' => '修改失败', 'status' => 1);
						}
					} else {
						$return = array('msg' => '两次密码输入密码不一至，或者为空', 'status' => 1);
					}
				} else {
					$return = array('msg' => '原密码错误', 'status' => 1);
				}
			} else {
				$return = array('msg' => '该用户不存在', 'status' => 1);
			}
			return json($return);
		}
	}

	/**
	 * 修改资料模板渲染
	 */
	public function edit($id) {
		$list = $this->admin->get($id);
		// $role = D("Role");
		 $roleres = $this->role->select();
		$this->assign(array(
			'title' => '修改管理员',
			'list' => $list,
            'roleres' => $roleres,
		));
		return $this->fetch();
	}

	/**
	 * 修改资料
	 */
	public function do_edit() {
		if (request()->isPost()) {
			$data = array(
				'name' => input('post.name'),
				'adminuser' => input('post.adminuser'),
				'mobile' => input('post.mobile'),
				'role_id' => input('post.role_id'),
				'status' => input('post.status'),
			);
				$res = $this->admin->save($data,['id' => input('post.id')]);
				if ($res) {
					//修改成功
					$redata = array(
						'status' => 0,
						'msg' => '修改成功',
					);
				} else {
					//修改失败
					$redata = array(
						'status' => 1,
						'msg' => '修改失败',
					);
				}
		}
		return json($redata);

	}

	/**
	 * 删除管理员
	 */
	public function del() {
		if (request()->isGet()) {
			$id = input('get.id');
			if ($id == 1) {
				return json(array('msg' => '超级管理员不能删除！', 'status' => 1));
			}
			$res = $this->admin->destroy($id);
			if ($res) {
				return json(array('msg' => '删除管理员成功！', 'status' => 0));
			} else {
				return json(array('msg' => '删除管理员失败！', 'status' => 1));
			}
		}
	}
	/**
	 * 启用管理员
	 */
	public function Enabled($id) {
		if (request()->isGet()) {
			$data['status'] = 0;
			$res = $this->admin->save($data,['id' => $id]);
			if ($res) {
				return json(array('msg' => '管理员启用成功！', 'status' => 0));
			} else {
				return json(array('msg' => '管理员启用失败！', 'status' => 1));
			}
		}
	}
	/**
	 * 停用管理员
	 */
	public function disable($id) {
		if (request()->isGet()) {
			$where['id'] = $id;
			$data['status'] = 1;
			if ($id == 1) {
				return json(array('msg' => '超级管理员不能停用！', 'status' => 1));
				exit();
			}
			$res = $this->admin->save($data,['id' => $id]);
			if ($res) {
				return json(array('msg' => '管理员停用成功！', 'status' => 0));
			} else {
				return json(array('msg' => '管理员停用失败！', 'status' => 1));
			}
		}
	}

	/**
	 *退出登录
	 * */
	public function out_login() {
		session('admin_uid', null);
		header('location: ' . url('Login/index'));
	}
}
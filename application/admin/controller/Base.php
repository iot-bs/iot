<?php
namespace app\admin\controller;
use think\Controller;

class Base extends Controller {
	/**
	 * 当前登录会员信息
	 * */
	protected $adminUser = [];
	public $admin_uid;
	public $admin;
	public $role;
	public $privilege;
	public function __construct() {
	    $this->admin = model('Admin');
	    $this->role = model('Role');
	    $this->privilege = model('Privilege');
		parent::__construct();
		$adminId = session('admin_uid');
		if (!session('admin_uid')) {
			$this->error('请先登录系统', url('Login/index'));
		}
		$this->admin_uid = $adminId;
		$this->assign('admin_uid',$this->admin_uid);
		if (request()->module() == 'Admin' && request()->controller() == 'Index') {
			$this->checkLogin();
			return true;
		}
		if (request()->module() == 'Admin' && request()->controller() == 'Admin' && request()->action() == 'logout') {
			return true;
		}
		 if (session("privilege")!='*' && !in_array(request()->module().'/'.request()->controller().'/'.request()->action(),session('privilege'))) {
		     if (request()->isAjax()) {
		         return json(array('msg' => '没有权限访问该功能！', 'status' => 1));
		     }else {
		         $this->error('没有权限访问该功能！');
		     }

		 }
		// var_dump(config("menu.ADMIN_LIST"));exit;
		// foreach (config("menu.ADMIN_LIST") as $k) {
		//     # code...
		//     foreach ($k['menu'] as $v) {
		//         # code...
		//         var_dump($v['name']); echo "<br>";
		//     }
		// }
		// exit;
		$this->assign('menu_list', config('menu.ADMIN_LIST'));
		// var_dump(config('menu.ADMIN_LIST'));exit;
		$this->checkLogin();
	}

	/**
	 * 检查是否登录后台
	 * @return void
	 * */
	protected function checkLogin() {
		$where['id'] = session('admin_uid');
		$this->adminUser = model('admin')->where($where)->find();
		if (!$this->adminUser) {
			//登录验证失败
			header('location: ' . url('login/index'));
			exit();
		}

		$this->assign('_admin_user', $this->adminUser);
	}

	/**
	 * 弹窗提示
	 * @param string $tips 提示语
	 * @return void
	 * */
	protected function tip($tips) {
		$this->assign('tips', $tips);
		echo $this->fetch('public::tip');
	}
    //微信我二维码配置
    public function wxConfig(){
        $access_token=cache("access_token");
        if(!$access_token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . config('service.APPID'). "&secret=" .  config('service.APPSECRET');
            $result=doCurl($url);
            $result_arr = json_decode($result);
            try{
                cache("access_token", $result_arr->access_token, $result_arr->expires_in);
            }catch (Exception $e)
            {
                throw new WeChatException();
            }

        }
    }

}
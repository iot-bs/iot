<?php
namespace app\index\controller;
use think\Controller;
class Index extends  Controller{
	public function index() {

        ;print_r(date('Y-m-d H:i:s',time()));
        echo "<br>";
        print_r(date('Y-m-d H:i:s',time()+3600*2));
        echo "<br>";
        ;print_r(time());
        echo "<br>";
        print_r(time()+3600*"2");
        header('location:http://www.lkgwxm.cn/admin');

	}

}

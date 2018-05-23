<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/21 0021
 * Time: 17:03
 */

namespace app\home\controller;
use think\Controller;

class Base extends Controller
{
    public $msg;
    public function initialize()
    {
        $this->msg = 'order';
    }
    //创建订单号
    public function setOrderSn(){
        list($t1,$t2) = explode(' ',microtime());
        $t3 = explode('.',$t1*10000);
        return $t2.$t3[0].(rand(10000,99999));
    }

}
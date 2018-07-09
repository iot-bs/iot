<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/25
 * Time: 14:55
 */

namespace app\home\controller;


class Index extends Base
{
//    protected $beforeActionList=[
//        'checkExclusiveScope' =>'',
//    ];
    public function index()
    {
        print_r(time());
        echo "<br>";
        print_r(1528954180+3600);
        echo "<br>";

        echo date("Y-m-d H:i:s",time());
        echo "<br>";
        echo date("Y-m-d H:i:s",1528954180);
        echo "<br>";
        echo date("Y-m-d H:i:s",1528954180+3600);
        exit;
        if(time() > (1528954180+3600))
        {

        }
    }
}
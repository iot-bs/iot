<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/11 0011
 * Time: 11:57
 */

namespace app\admin\controller;


class Emergency extends Base
{
    public $obj;
    public function initialize()
    {
        $this->obj = db('Emergency');
    }
    public function index()
    {
        $type = input('get.type','1');
//        $list = $this->obj->getallEmergency($type);
        $list = $this->obj->where(['c_status' => $type])->paginate();
        return $this->fetch('',
            [
                'title' => '紧急充电',
                'list' => $list,
                'type' => ['1','2'],
                'types' =>$type,
            ]);
    }
    public function status()
    {
        $id = input('get.id');
        if(empty($id))
        {
            return show(1,'缺少id');
        }
        $res = $this->obj->update(['c_status' => 2,'c_id' => $id]);
        if($res)
        {
            return show(0,'sucesss');
        }
        return show(1,'error');
    }
}
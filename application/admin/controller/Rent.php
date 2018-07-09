<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/12 0012
 * Time: 16:53
 */

namespace app\admin\controller;


class Rent extends Base
{
    public $obj;
    public function initialize()
    {
        $this->obj = model('Rent');
    }
    public function index()
    {
        $status = input('get.status',0);
        $so = input('get.so','');
        $where = [];
        if(!empty($so))
        {
            $where[] = ['c_username' ,'like',"%".$so."%"];
        }
        $statuss = [0,1,2];
        $where[] = ['c_status' ,'=',$status];
        $list = $this->obj->getAllRents($where);
        return $this->fetch('',[
            'list' => $list,
            'status' => $status,
            'statuss' => $statuss,
            'so' => $so,
            'title' => '租用设备'
        ]);
    }
    //激活
    public function isStatus()
    {
        $id = input('get.id');
        $device = $this->obj->get($id);
        if($device['c_status'] == 0)
        {
            $data = ['c_status' => 1];
        }
        elseif ($device['c_status'] == 1)
        {
            $data = ['c_status' => 0];
        }else
        {
            return show(0,'已结束的设备不需要激活');
        }
        $res = $this->obj->save($data ,['c_id' => $id]);
        if($res)
        {
            return show(1,'激活成功');
        }
        return show(0,'激活失败');

    }
    public function del()
    {
        $id = input('get.id');
        $res = $this->obj->destroy($id);
        if($res)
        {
            return show(1,'删除成功');
        }
        return show(1,'删除失败');
    }
    public function over()
    {
        $id = input('get.id');
        $data = ['c_status' => 2];
        $res = $this->obj->save($data,['c_id' => $id]);
        if($res)
        {
            return show(1,'结束订单成功');
        }
        return show(1,'结束订单失败失败');
    }
}
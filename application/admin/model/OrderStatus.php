<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/22
 * Time: 17:28
 */

namespace app\admin\model;
use think\Model;

class OrderStatus extends Model
{
    protected $hidden = ['c_isdel','c_stop_time','c_id'];
    public function devices()
    {
        return $this->belongsTo('Device','c_device_id','c_deviceid');
    }
    public function orders()
    {
        return $this->belongsTo('Order','c_order_id','c_id');
    }
    public function users(){
        return $this->belongsTo('User','c_user_id','c_id');
    }

    protected $pk = 'c_id';
    public function getOrderStatus($where)
    {
        $res = $this->where($where)->paginate();
        return $res;
    }
    //新增订单
    public function add($data)
    {
        $data['c_status'] = 0;
        $res = $this->allowField(true)->save($data);
        return $res;
    }

    /**
     * 用户查询自己的租用的设备的状态 status = 0 status = 1 status =2
     * @param $id
     * @return array
     */
    public function getOrderStatusByUserId($id,$page = 1,$size = 15)
    {
        $where[] = ['c_user_id','=',$id];
        $where[] = ['c_status','<>',0];
//        $where[] = ['c_status','neq',0];
//        $where['c_status'] = array('neq',0);
        return $this->with('devices')
            ->where($where)
            ->order('c_id','desc')
            ->paginate($size,false,['page'=>$page]);
    }
    //获取充电桩状态
    public function getStatus($userid,$orderid)
    {
        $where[] = ['c_order_id','=',$orderid];
        $where[] = ['c_user_id','=',$userid];
        $where[] = ['c_status','=','1'];
        return $this->with(['orders','devices'])->where($where)->find();
    }
    /*
    * 更新订单状态表数据 用于支付成功后的订单表修改
    */
    public function updateStatusByOrderId($orderId)
    {
        $data['c_start_time'] = time();
        $data['c_status'] = 1;
        return $this->save($data,['c_order_id' =>$orderId]);
    }

}
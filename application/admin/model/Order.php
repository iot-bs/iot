<?php
namespace app\admin\model;

use think\Model;

class Order extends Model
{
    protected $pk = 'c_id';
    //订单外键 c_order_sn 对应device表 c_devicesn  设备编号作为外键
    public function devices()
    {
        return $this->belongsTo('Device','c_sn','c_devicesn');
    }
    public function users(){
        return $this->belongsTo('User','c_user_id','c_id');
    }
    public function getOrder($where)
    {
        $res = $this->with(['devices','users'])->where($where)->paginate();
        return $res;
    }
}
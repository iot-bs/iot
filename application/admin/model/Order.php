<?php
namespace app\admin\model;

use think\Model;
use think\Log;

class Order extends Model
{
    protected $pk = 'c_id';
    protected $autoWriteTimestamp = true;
    //订单外键 c_order_sn 对应device表 c_devicesn  设备编号作为外键
    public function devices()
    {
        return $this->belongsTo('Device','c_device_id','c_deviceid');
    }
    public function users(){
        return $this->belongsTo('User','c_user_id','c_id');
    }
    public function getOrder($where = [])
    {
        $where[] = ['c_status','=',1];
        $res = $this->with(['devices','users'])
                    ->where($where)
                    ->order('c_id','desc')
                    ->paginate();
        return $res;
    }
    //通过id查询订单
    public function getOrderById($id)
    {
        $res = $this->get($id);
        return $res;
    }
    public function getOrderByWhere($where)
    {
        $res = $this->where($where)->find();
        return $res;
    }
    /**
     * 订单插入
     * @param $data
     */
    public function add($data)
    {
        $data['c_status'] = 1;
        $data['c_pay_status'] = 0;
        $data['createtime'] = time();
        $res = $this->save($data);
        return $res;
    }
    /*
     * 更新订单表数据 用于支付成功后的订单表修改
     */
    public function updateOrderSn($sn,$weixindata)
    {
        if(!empty($weixindata['transaction_id']))
        {
            $data['c_transaction_id'] = $weixindata['transaction_id'];
        }
        if(!empty($weixindata['total_fee']))
        {
            $data['c_pay_amount'] = $weixindata['total_fee']/100;
            $data['c_pay_status'] = 1;
        }
        if(!empty($weixindata['time_end']))
        {
            $data['c_pay_time'] = $weixindata['time_end'];
        }
        $res = $this->allowField(true)->save($data,['c_sn'=>$sn]);
        return $res;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/21 0021
 * Time: 17:01
 */

namespace app\home\controller;


use think\Validate;
use think\Db;
class Order extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'payorder'],
    ];
    public function index()
    {
        return $this->fetch();
    }
    /**
     * $cart =
     * {"c_user_id": "4", 用户id
    "c_deviceid: "19", 设备id  => 商品id
    "c_charge_id": "5", 充电时间id  充电价格类型
    "c_charge_time": "1", 充电时间
    "c_price": "2", 价格
    "c_type": "1" 租用leix
    "c_merchant_id" "1"
    }

     */
    //加入购物车
    public function payOrder()
    {
        if(request()->isPost())
        {
            //对提交的订单进行检查
            $data = input('post.');
            $validate = validate('Order');
            if(!isset($data['deviceid']) || !isset($data['user_id']) || !isset($data['charge_id']))
            {
                return show(0,'缺少参数');
            }
            $postData['c_device_id'] = $data['deviceid'];
            $postData['c_user_id'] = $data['user_id'];
            $postData['c_charge_id'] = $data['charge_id'];
            $res = $validate->scene('post')->check($postData);
            if (!$res) {
                return show(0, $validate->getError());
            }
            //判断设备是否合法
            $device = $this->device->getDeviceById($postData['c_device_id']);
            if (empty($device)) {
                return show(0, '设备编号不存在');
            }
            // 对设备进行检查
            $deviceStatus = \app\common\lib\Device::getInstance()->getDeviceBySn($device['c_devicesn']);
             if(empty($deviceStatus))
            {
                return show(0,'设备未开机');
            }
            //判断充电单价 id 是否合法和存在
            $charge = $this->charge->getChargeById($postData['c_charge_id']);
            if (empty($charge)) {
                return show(0, '充电单价id不存在，类型错误');
            }
            if($deviceStatus[$charge['c_type']+1] == '1')
            {
                return show(0, '该设备正在使用');
            }
            //判断user用户是否存在
            $user = $this->user->getUserById($postData['c_user_id']);
            if (empty($user)) {
                return show(0, '用户不存在');
            }
            $postData['c_username'] = $user['c_username'];
            //获取用户来源url
            $postData['c_referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            //创建爱你订单号
            $orderSn = $this->setOrderSn();
            $postData['c_sn'] = $orderSn;
            $postData['c_charge_time'] = $charge['c_charge_time'];
            $postData['c_total_price'] = doubleval($charge['c_price']);
            $postData['c_charge_type'] = $charge['c_type'];
            $postData['c_status'] = 1;
            //再次判断是否正确的数据
            $prepay = $validate->scene('prepay')->check($postData);
            if (!$prepay) {
                return show(0, $validate->getError());
            }
            Db::startTrans();
            try {
                $this->order->add($postData);
                $orderId = $this->order->c_id;
                //写入订单状态表
                $postData['c_order_id'] = $orderId;
                $postData['c_last_time'] = $postData['c_charge_time'];
                $postData['c_type'] = $charge['c_type'];
                $statusId = $this->orderStatus->add($postData);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return show(0, '订单处理失败');
            }
            $pay = new Pay();
            return $pay->prePay($orderId,$postData['c_user_id']);
        }
        return json([
            'status' => 0,
            'msg' => '非法请求'
        ]);
    }

}
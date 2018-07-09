<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/23
 * Time: 17:03
 */

namespace app\home\controller;
use app\wxpay\database\WxPayResults;
use app\wxpay\NativePay;
use app\wxpay\database\WxPayUnifiedOrder;
use app\wxpay\WxPayConfig;
use app\wxpay\WxPayApi;

class Weixinpay extends Base
{
    //微信支付的回调方法
    public function notify()
    {
        //获取回调的xml数据
        $data = file_get_contents("php://input");
        file_put_contents('/tmp/2.txt',$data,FILE_APPEND);
        try{
            $resultObj  = new WxPayResults();
            $weixinData = $resultObj->Init($data);
        }catch (\Exception $e)
        {
            return false;
         }
        if($weixinData['return_code'] === 'FAIL' || $weixinData['return_code'] !== 'SUCCESS')
        {
            return false;
        }
        $outTradeNo = $weixinData['out_trade_no'];
        $order = $this->order->getOrderByWhere(['c_sn' => $outTradeNo]);
        //查询订单是否存在
        if(!$order)
        {
            return true;
        }
        //更新订单表
        try{
            $orderRes = $this->order->updateOrderSn($outTradeNo,$weixinData);
        }catch (\Exception $e)
        {
            return false;
        }
        //更新充电状态表
        $status = $this->orderStatus->where('c_order_id',$order['c_id'])->find();
        if($status['c_start_time'] == 0)
        {
            $orderStatusRes = $this->orderStatus->updateStatusByOrderId($order['c_id']);
            if(!$orderStatusRes)
            {
                return false;
            }
        }
        try{
            $device = $this->device->get($order['c_device_id']);
            $relay['c_deviceid'] = $device['c_deviceid'];
            $relay['c_devicesn'] = $device['c_devicesn'];
            $type = $this->orderStatus->where('c_order_id',$order['c_id'])->find();
            \app\common\lib\Device::getInstance()->startCharge($relay,$type['c_type']);
        }catch (\Exception $e)
        {


        }

        return true;
    }

}
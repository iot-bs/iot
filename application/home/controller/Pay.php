<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/23
 * Time: 14:50
 */

namespace app\home\controller;
use app\wxpay\JsApiPay;
use app\wxpay\NativePay;
use app\wxpay\database\WxPayUnifiedOrder;
use app\wxpay\WxPayConfig;
use app\wxpay\WxPayApi;

/**
 * 支付模块
 * Class Pay
 * @package app\home\controller
 */
class Pay extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'payorder'],
    ];
    /**
     * @param $id 订单id
     * //返回支付二维码
     * @return string
     */
    public function prePay($orderId, $userId)
    {
        //判断用户是否为此订单用户
        /**
         * 判断订单状态是否正常
         * 1.订单是否存在
         * 2.订单状态是否正常
         * 3.订单是否已经支付 订单状态
         */

        $orderId = intval($orderId);
        if(empty($orderId))
        {
            return show(0,'请求不合法');
        }
        $order = $this->order->getOrderById($orderId);
        if(empty($order))
        {
            return show(0,'订单不存在');
        }
        if($order['c_user_id'] != $userId)
        {
            return show(0,'用户非法操作');
        }
        if($order['c_status'] != 1 || $order['c_pay_status'] != 0)
        {
            return show(0,'订单状态不正常');
        }
        $device =$this->device->getDeviceById($order['c_device_id']);
        //根据用户id获取openid
        $user = $this->user->getUserById($userId);
        $openid = $user['c_openid'];
        if(empty($openid))
        {
            return show(0,'用户openid异常');
        }
        if(empty($device))
        {
            return show(0,'设备异常');
        }
//        try{
            $input = new WxPayUnifiedOrder();
            $input->setBody($device['c_name']);
            $input->setAttach($device['c_name']);
            $input->setOutTradeNo($order['c_sn']);
            $input->setTotalFee($order['c_total_price']*100);
            $input->setTimeStart(date("YmdHis"));
            $input->setTimeExpire(date("YmdHis", time() + 600));
            $input->setGoodsTag("QRcode");
            $input->setNotifyUrl(config('pay.notifyUrl'));
            $input->setTradeType("JSAPI");
            $input->setProductId($order['c_device_id']);
            $input->setOpenid($openid);
            $order = WxPayApi::unifiedOrder($input);
            $notify = new JsApiPay();
            $result = $notify->getJsApiParameters($order);
            if(empty($result)) {
                return show(0,'订单异常');
            }
//        }catch (\Exception $e)
//        {
//            return show(0,'订单异常',$e);
//        }
        return show(1,'sucess',['param' => $result,'orderid' => $orderId]);
    }
}
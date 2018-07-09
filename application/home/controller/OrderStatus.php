<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/29
 * Time: 17:19
 */

namespace app\home\controller;


class OrderStatus extends Base
{
    public static $A1 = 13;
    public static $A2 = 12;
    public static $A3 = 11;
    public static $A4 = 10;
    public static $B1 = 0.2;
    public static $B2 = 0.7;
    public static $B3 = 0.1;
    public static $D = 1;
    protected $beforeActionList=[
        'checkExclusiveScope' => ['only' => 'getstatus'],
    ];
    //获取充电状态
    public function getStatus()
    {
        if(request()->isPost()){
            $userid = input('post.user_id');
            $orderid = input('post.orderid');
            if(empty($userid) || empty($orderid))
            {
                return show(0,'缺少参数');
            }
            $res = $this->orderStatus->getStatus($userid,$orderid);
            if(empty($res))
            {
                return show(0,'不存在的订单');
            }
            //获取设备的状态 是否正常和异常
            $warn = \app\common\lib\Device::getInstance()->getWarn($res['devices']['c_devicesn']);
            $monitor = \app\common\lib\Device::getInstance()->getMonitor($res['devices']['c_devicesn']);
//            return json(date('m',time()));
            if($monitor)
            {
                $temp = $monitor['c_temp'];
                $current = $monitor['c_current'][1]['Value'];
                $voltage = $monitor['c_voltage'][0]['Value'];
            }else{$temp = '0';$current = '0'; $voltage = '0';}
            $power = $this->getPower($monitor,$res['devices']['c_devicesn']);
            if($warn)
            {
                $warnType = unserialize($warn['warning']);
                $warnStr = $warnType['WarnType'] == 'Current'?'电流异常':($warnType['WarnType' == 'Voltage']?'电压异常':($warnType['WarnType'] == 'Temp'?'温度异常':'正常'));

            }
            else{$warnStr = '正常';}
            $list['charge_time'] = $res['c_charge_time'];
            $list['last_time'] = ($res['c_start_time']+3600*$res['c_charge_time'])-time();;
            $list['devicesn'] = $res['devices']['c_devicesn'];
            $list['total_price'] =$res['orders']['c_total_price'];
            $list['status'] = $res['c_status'];
            $list['current'] = $current;
            $list['temp'] = $temp;
            $list['voltage'] = $voltage;
            $list['power'] = $power;
            $list['deviceStaus'] = $warnStr;
            return show(1,'sucess',$list);
        }
        return show(0,'非法请求');
    }
    //获取剩余电量
    public function getPower($monitor,$sn)
    {
        try
        {
            $safe = $this->safelimit->where('c_devicesn' ,$sn)->find();
            $temp = $monitor['c_temp'];
            $current = $monitor['c_current'][1]['Value'];
            $voltage = $monitor['c_voltage'][0]['Value'];
            $c = $current < 2 ?1:(($current>2||$current<10)?0.8:($current>10?0.6:0));
            $f = $voltage;
            $power = 0;
            if($f > $safe['c_a4'] && $f < $safe['c_a3'])
            {
                $power = (($f - $safe['c_a4'])/($safe['c_a3'] - $safe['c_a4']))*$safe['c_b3']*$c*self::$D;
            }else if($f > $safe['c_a3'] && $f < $safe['c_a2'])
            {
                $power = (($f - $safe['c_a3'])/($safe['c_a2'] - $safe['c_a3']))*$safe['c_b2']*$c*self::$D+$safe['c_b3'];
            }else if ($f > $safe['c_a2'] && $f < $safe['c_a1'])
            {
                $power = (($f - $safe['c_a2'])/($safe['c_a1'] - $safe['c_a2']))*$safe['c_b1']*$c*self::$D + $safe['c_b3'] + $safe['c_b2'];
            }
        }catch (\Exception $e)
        {
            //出现异常就使用默认配置
            $temp = $monitor['c_temp'];
            $current = $monitor['c_current'][1]['Value'];
            $voltage = $monitor['c_voltage'][0]['Value'];
            $d = 1;
            $c = $current < 2 ?1:(($current>2||$current<10)?0.8:($current>10?0.6:0));
            $f = $voltage;
            $power = 0;
            if($f > self::$A4 && $f < self::$A3)
            {
                $power = (($f - self::$A4)/(self::$A3 - self::$A4))*self::$B3*$c*$d;
            }else if($f > self::$A3 && $f < self::$A2)
            {
                $power = (($f - self::$A3)/(self::$A2 - self::$A3))*self::$B2*$c*$d+self::$B3;
            }else if ($f > self::$A2 && $f < self::$A1)
            {
                $power = (($f - self::$A2)/(self::$A1 - self::$A2))*self::$B1*$c*$d+self::$B3+self::$B2;
            }
        }
        return sprintf("%.2f",$power*100);
    }
}
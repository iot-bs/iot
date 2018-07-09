<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/29
 * Time: 14:45
 */

namespace app\common\lib;
use app\service\Service;

class Device
{
    /**
     * @var null
     * 单例模式
     */
    private static $_instance = null;
    public static function getInstance()
    {
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function getOnlineDevice()
    {
        //获取在线的设备和状态
        $monitors = Service::getInstance()->call("Device::getDevicesFromClient")->getResult(10);
        return $monitors;
    }
    public function getDeviceDetailBySn($sn)
    {
        //微信端根据设备id获取在线状态
        $monitor = Service::getInstance()->call("Device::getMonitorBySn",$sn)->getResult(10);
        return $monitor;
    }

    //获取温度电流电压等信息
    public function getMonitor($sn)
    {
        $monitor = Service::getInstance()->call("Device::getMonitorBySn",$sn)->getResult(10);
        if($monitor)
        {
            $monitor['c_current'] = unserialize($monitor['c_current']);
            $monitor['c_voltage'] = unserialize($monitor['c_voltage']);
        }

        return $monitor;
    }
    public function getWarn($sn)
    {
        $warns = Service::getInstance()->call("Monitor::getWarns",$sn)->getResult(10);
        return $warns;
    }
    public function getDeviceBySn($sn)
    {
        $device = $this->getDeviceDetailBySn($sn);
        if(empty($device))
        {
            return false;
        }
        $relay = unserialize($device['c_relay']);
        return $relay;
    }
    //开始充电
    public function startCharge($device, $type)
    {
        $device['c_relay'] = [$type+1 => '1'];
        Service::getInstance()->call("Control::update",$device,$type)->getResult(10);
    }
    //扫码开启继电器一
    public function openRelayByScan($sn)
    {
        Service::getInstance()->call('Control::openRelayByScan',$sn)->getResult(10);
    }
    //租用设备控制开关接口
    public function controlRelay($sn, $relay)
    {
         $res = Service::getInstance()->call('Control::rentControl',$sn,$relay)->getResult(10);
        return $res;
    }

}
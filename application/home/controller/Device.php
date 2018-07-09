<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/28
 * Time: 16:44
 */
namespace app\home\controller;
use app\service\Service;
class Device extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'getalldevices,getdetailbyid,getdevicestatus'],
    ];
    //首页获取所有在线设备
    public function getAllDevices()
    {
        $res = \app\common\lib\Device::getInstance()->getOnlineDevice();
        if(empty($res))
        {
            return show(0,'设备未上线');
        }
        $deviceStatus = $res['deviceStatus'];
        $monitorStatus = $res['monitorStatus'];
        $list = [];
        $i = 0;
        foreach ($deviceStatus as $k => $v)
        {
            if (array_key_exists($k, $monitorStatus)) {
                $device = $this->device->getDeviceBySn($k);
                if($device['c_type'] == '3')
                {
                    continue;
                }
                $list[$i]['sn'] = $k;
                $list[$i]['id'] = $device['c_deviceid'];
                $list[$i]['lng'] = $monitorStatus[$k]['c_lng'];
                $list[$i]['lat'] = $monitorStatus[$k]['c_lat'];
            }
            $i++;
        }
        return show(1,'success',$list);
    }
    //根据id获取详细设备信息
    public function getDetailById($id)
    {
        //判断是否是整型
        if(!is_numeric($id) || !is_int($id+0) || !(($id+0)>0)){
            return show(0,'id 不是数字');
        }
        $res = $this->device->getDeviceById($id);
        if(empty($res))
        {
            return show(0,'设备不存在');
        }
        if($res['c_type'] == '3')
        {
            return show(0,'该设备类型不能进行扫码充电');
        }
        $res->hidden(['c_qr_code','c_add_time','c_isdel','c_lease_status','c_status']);
        $device = \app\common\lib\Device::getInstance()->getDeviceDetailBySn($res['c_devicesn']);
        if(empty($device))
        {
            return show(0,'设备不在线');
        }
        \app\common\lib\Device::getInstance()->openRelayByScan($res['c_devicesn']);
        $res['c_lng'] = $device['c_lng'];
        $res['c_lat'] = $device['c_lat'];
        return show(1,'success',$res);
    }
    //检查设备是否可用
    public function getDeviceStatus($deviceid)
    {
        $res = $this->device->getDeviceById($deviceid);
        if(empty($res))
        {
            return show(0,'设备不存在');
        }
        $status = \app\common\lib\Device::getInstance()->getDeviceDetailBySn($res['c_devicesn']);
        if(empty($status))
        {
            return show(0,'设备未开机');
        }
        $relay = unserialize($status['c_relay']);
        unset($relay['1']);
        $devicestaus['1'] = $relay['2'] == '0' ?'1':'0';
        $devicestaus['2'] = $relay['3'] == '0'?'1':'0';
        return show(1,'sucess',$devicestaus);
    }
}
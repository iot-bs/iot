<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/12 0012
 * Time: 15:35
 */

namespace app\home\controller;


use app\service\Token;

class Rent extends Base
{
    public static $D = 1;
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'controlrelay,active,getrentstatus'],
    ];
    public function controlRelay()
    {
        $data = input('post.');

        if(empty($data['id']))
        {
            return show(0,'缺少租用id');
        }
        $rent = $this->rent->get($data['id']);
        if($rent['c_status'] != '1'){
            return show(0,'设备未激活或者已结束');
        }
        $relay = $data['relay'];
        $device = $this->device->getDeviceById($rent['c_deviceid']);
        if(empty($device))
        {
            return show(0,'设备不存在或者不在线');
        }

        //暂停更新数据库时间
        if($relay == 0)
        {
            $time = $rent['c_chargetime'] + (time()-$rent['c_starttime']);
            $this->rent->updateTime($rent['c_id'],['c_starttime' => time(),'c_chargetime' => $time]);
        }else if($relay == 1)//开启更新时间
        {
            $this->rent->updateTime($rent['c_id'],['c_starttime' => time()]);
        }

        $res = \app\common\lib\Device::getInstance()->controlRelay($device['c_devicesn'],$relay);
        if($res)
        {
            return show(1,'sucess');
        }
        return show(0,'控制失败');
    }

    /**
     * 租用设备激活
     * deviceid
     * name
     * phone
     */
    public function active()
    {
        $uid = Token::getCurrentUid();
        $data = input('post.');
        //检查设备id是否存在
        $device = $this->device->get($data['deviceid']);
        if(empty($device))
        {
            return show(0,'设备不存在');
        }
        $rent = $this->rent->getRentByStatus([['c_deviceid','=',$data['deviceid']],['c_status' ,'=', 1]])->toArray();
        if(!empty($rent))
        {
            return show(0,'该设备已被租用');
        }
        if(empty($data['phone']))
        {
            return show(0,'请填写电话号码');
        }
        $rent['c_userid'] = $uid;
        $rent['c_phone'] = $data['phone'];
        $rent['c_deviceid'] = $data['deviceid'];
        $rent['c_username'] = $data['name'];
        $rent['c_addtime'] = time();
        $res = $this->rent->add($rent);
        if($res)
        {
            return show(1,'申请成功');
        }
        return show(0,'申请失败');

    }
    public function getRentStatus()
    {
        //获取设备的状态 是否正常和异常
        $id = input('get.id');
        if(empty($id))
        {
            return show(0,'缺少租用id');
        }
        $rent = $this->rent->get($id);
        if(empty($rent))
        {
            return show(0,'该租用不存在');
        }
        $device = $this->device->getDeviceById($rent['c_deviceid']);
        if(empty($device))
        {
            return show(0,'设备不存在');
        }
        $warn = \app\common\lib\Device::getInstance()->getWarn($device['c_devicesn']);
        $monitor = \app\common\lib\Device::getInstance()->getMonitor($device['c_devicesn']);
        if($monitor)
        {
            $temp = $monitor['c_temp'];
            $current = $monitor['c_current'][1]['Value'];
            $voltage = $monitor['c_voltage'][0]['Value'];
        }else{
            return show(0,'设备不在线');
        }
        $power = $this->getPower($monitor,$device['c_devicesn']);
        if($warn)
        {
            $warnType = unserialize($warn['warning']);
            $warnStr = $warnType['WarnType'] == 'Current'?'电流异常':($warnType['WarnType' == 'Voltage']?'电压异常':($warnType['WarnType'] == 'Temp'?'温度异常':'正常'));

        }
        else{$warnStr = '正常';}
        $list['deviceid'] = $device['c_deviceid'];
        $list['devicesn'] = $device['c_devicesn'];
        $list['current'] = $current;
        $list['temp'] = $temp;
        $list['voltage'] = $voltage;
        $list['power'] = $power;
        $list['deviceStaus'] = $warnStr;
        $status = \app\common\lib\Device::getInstance()->getDeviceDetailBySn($device['c_devicesn']);
        if(empty($status))
        {
            $list['relay'] = '2';
        }else {
            $temp = unserialize($status['c_relay']);
            if ($temp['1'] == '1') {
                $list['relay'] = '1';
            } else {
                $list['relay'] = '0';
            }
        }
        return show(1,'sucess',$list);
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
                $power = (($f - $safe['c_a4'])/($safe['c_a3'] - $safe['c_a4']))*$safe['c_b3']*$c*($safe['c_d']?$safe['c_d']:self::$D);
            }else if($f > $safe['c_a3'] && $f < $safe['c_a2'])
            {
                $power = (($f - $safe['c_a3'])/($safe['c_a2'] - $safe['c_a3']))*$safe['c_b2']*$c*($safe['c_d']?$safe['c_d']:self::$D)+$safe['c_b3'];
            }else if ($f > $safe['c_a2'] && $f < $safe['c_a1'])
            {
                $power = (($f - $safe['c_a2'])/($safe['c_a1'] - $safe['c_a2']))*$safe['c_b1']*$c*($safe['c_d']?$safe['c_d']:self::$D) + $safe['c_b3'] + $safe['c_b2'];
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
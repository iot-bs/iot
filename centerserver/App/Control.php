<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use Lib;
use model\Device as DbDevice;
use Lib\Monitor as Mon;
use Lib\Tasks;
use Lib\Robot;
use Lib\Client;
use Lib\Util;

class Control {

    /**
	 * 获取代理服务器
	 * @return array
	 */
	public static function getControls($gets = [], $page = 1, $pagesize = 10) {
        echo '----------------monitor table'.PHP_EOL;
        $allDeviceStatus = Lib\Robot::$table;
        $devices = [];
        foreach($allDeviceStatus as $k => $v){
            $devices[$k] = $v;
        }
        $list =  Lib\Monitor::$table;
        $monitors = [];
        foreach ($list as $k => $v) {
            $monitors[$k] = $v;
        }
        return ['deviceStatus' => $devices,'monitorStatus' => $monitors];
		return $res;
	}
	public static function preOrderCheck($data){
	    $devicesn = $data['c_devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('5',['DeviceSn' => $devicesn]);
        $client = new Client($devicesn);
        $client->control($call);
        return true;
    }

    /**
     * 电流电压温度阈值控制
     * @param $data
     */
    public static function doLimit($data){
        $devicesn = $data['DeviceSn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = $data;
        $client = new Client($devicesn);
        $client->control($call);
        switch ($data['ServerControl']){
            case '10':
                sleep(2);
                $current = \Table\SafeLimit::$table->get($devicesn);
                $current = unserialize($current['safe_limit']);
                if($current){
                    if($current['CurrentCon']['No'] == $data['CurrentCon']['No']){
                        if($current['ControlStatus'] == '1'){
                            return true;
                        }
                    }
                }
                $res = false;
                break;
            case '11':
                sleep(2);
                $vdc = \Table\SafeLimit::$table->get($devicesn);
                $vdc = unserialize($vdc['safe_limit']);
                if($vdc){
                    if($vdc['VdcCon']['No'] == $data['VdcCon']['No']){
                        if($vdc['ControlStatus'] == '1'){
                            return  true;
                        }
                    }
                }
                $res = false;
                break;
            case '12':
                sleep(2);
                $temp = \Table\SafeLimit::$table->get($devicesn);
                $temp = unserialize($temp['safe_limit']);
                echo "--------------------".PHP_EOL;
                print_r($temp);
                print_r($data);
                echo "---------------------";
                if($temp){
                    if($temp['TempCon']){
                        if($temp['ControlStatus'] == '1'){
                            return true;
                        }
                    }
                }
                $res = false;
                break;
            default:
                $res = false;
                break;
        }
        return $res;
    }
	/**
	 *  修改任务
	 * @param $id
	 * @param $Device
	 * @return array
	 */
	public static function update($data) {
		echo "APP ------ Control ----------updateDevice" . PHP_EOL;
		if (empty($data['c_deviceid']) && empty($data)) {
			return false;
		}
		$ret = Tasks::updateRelay($data);
		if(!$ret){
			return false;
		}
		return true;
	}
	/**
	 * @param    [type]      $data [description]
	 * @return   [type]            [description]
	 */
	public static function contype($data) {
		echo "APP ------ Control ----------updateDevice" . PHP_EOL;
		if (empty($data['c_devicesn']) && empty($data)) {
			return false;
		}
		$ret = Tasks::contype($data);
		if(!$ret){
			return false;
		}
		return true;
	}

    /**
     * 心跳设置
     * @param $data
     */
	public static function heartSet($data){
        $devicesn = $data['devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('4',['DeviceSn' => $devicesn,'Heartbeat' => $data['heart']]);
        $client = new Client($devicesn);
        $client->control($call);
        sleep(3);
        $heart = \Table\SafeLimit::$table->get($devicesn);
        $heart = unserialize($heart['safe_limit']);
        print_r($heart);
        if($heart){
            if($heart['RequestControl']== '2'){
                if($heart['ControlStatus'] == '1'){
                    return  true;
                }
            }
        }
       return false;
    }
	/**
	 *del the device
	 * @param    [type]      $id [deviceid]
	 * @return   [type]          [boolean]
	 */
	public static function delDevice($id) {
		echo "APP ------ Device ----------delDevice" . PHP_EOL;
		if (empty($id)) {
			return false;
		}
		$res = Lib\Robot::delAgent($id);
        $res1 = DbDevice::getInstance()->delDevice($id);
		if ($res && $res1) {
			return true;
		}
		return false;
	}


}
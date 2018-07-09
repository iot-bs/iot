<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use Lib;
use model\Device as DbDevice;
use Table\SafeLimit;
use Table\Warning;

class Device {
    /**
     * 内存表测试
     */
    public static function test()
    {
        $monitor = [];
        foreach (Lib\Monitor::$table as $k => $v)
        {
            $monitor[$k] = $v;
        }
        $task = [];
        foreach (Lib\Tasks::$table as $k => $v)
        {
            $task[$k] = $v;
        }
        $robot = [];
        foreach (Lib\Robot::$table as $k => $v)
        {
            $robot[$k] = $v;
        }
        $safe = [];
        foreach (SafeLimit::$table as $k => $v)
        {
            $safe[$k] = $v;
        }
        $warning = [];
        foreach (Warning::$table as $k => $v) {
            $warning[$k] = $v;
        }
        return ['Monitor' => $monitor ,'Task' =>$task ,'Robot' => $robot, 'SafeLimit' =>$safe,'heartbeat' => Lib\CenterServer::$_server->heartbeat(false),'warning' => $warning];

    }
    //    根据设备编号获取设备
    public static  function getDeviceBySn($sn)
    {
        if($sn)
        {
            return false;
        }
        $res = Lib\Robot::$table->get($sn);
        return $res;
    }
	/**
	 * 获取设备
	 * @return array
	 */
	public static function getDevices($gets = [], $page = 1, $pagesize = 10) {
        $allDeviceStatus = Lib\Robot::$table;
		$res = [];
		foreach($allDeviceStatus as $k => $v){
		    $res[$k] = $v;
        }
        return $res;
    }

    /**
     * 微信端获取在线实时的设备
     */
    public static function getDevicesFromClient()
    {
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
    }

    /**
     * 微信根据设备id获取在线设备详细信息
     * @param $sn
     * @return array|bool
     */
    public static function getMonitorBySn($sn)
    {
        if(empty($sn))
        {
            return false;
        }
        $device =  Lib\Monitor::$table->get($sn);
        return $device;
    }

	/**
	 * 添加任务
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-10
	 * @param    [type]      $Device [true]
	 */
	public static function addDevice($data) {
		echo "APP ------ Device ----------addDevice" . PHP_EOL;
		if (empty($data)) {
			return false;
		}
		$id = DbDevice::getInstance()->insertDevice($data);
		if ($id) {
			//重新加载代理
			if (Lib\Robot::$aTable->set($id, ["devicesn" => $data['c_devicesn']])) {
				return $id;
			} else {
				DbDevice::getInstance()->delDevice($id);
				return false;
			}

		}
		return false;
	}

	/**
	 *  修改任务
	 * @param $id
	 * @param $Device
	 * @return array
	 */
	public static function updateDevice($id) {
		echo "APP ------ Device ----------updateDevice" . PHP_EOL;
		if (empty($id)) {
			return false;
		}
		$dev = DbDevice::getInstance()->getOneDevice($id);
		$status = $dev['c_status'];
		if ($status == 0) {
			$data['c_status'] = 1;
			$res = Lib\Robot::stopAgent($id);
		} else {
			$data['c_status'] = 0;
			$res = Lib\Robot::startAgent($id);
		}
		$data['c_deviceid'] = $id;
		$res1 = DbDevice::getInstance()->updateDevice($id,$data);
		if ($res && $res1) {
			return true;
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
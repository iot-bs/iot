<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use Lib;
use model\Device as DbDevice;
use model\Monitor as DbMonitor;
use Lib\Monitor as Mon;

class Monitor
{



	/**
	 * 获取代理服务器
	 * @return array
	 */
	public static function getMonitors($gets = [], $page = 1, $pagesize = 10) {
		// $list = DbDevice::getAllDevices();
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
	}
    public static function getMonitor($devicesn){
        return Lib\Monitor::$table->get($devicesn);
    }

    public static function getWarns($devicesn)
    {
        return \Table\Warning::$table->get($devicesn);
    }
}
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
		$list =  DbDevice::getInstance()->getOneColumns([],'c_deviceid,c_devicesn,c_status,c_type');
		foreach ($list as $k => $task) {
			$tmp = Lib\Robot::$table->get($task["c_devicesn"]);
			$monitor = Lib\Monitor::$table->get($task['c_devicesn']);
			if (!empty($tmp)) {
				$list[$k]["lasttime"] = $tmp["lasttime"];
				$list[$k]["isconnect"] = 1;
			} else {
				$list[$k]["isconnect"] = 0;
			}
			$list[$k]['monitor'] = $monitor;
		}
		return $list;
	}
    public static function getMonitor($devicesn){
        return $monitor = Lib\Monitor::$table->get($devicesn);
    }


}
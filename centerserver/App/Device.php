<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 */

namespace App;
use Lib;
use model\Device as DbDevice;

class Device {

	/**
	 * 获取设备
	 * @return array
	 */
	public static function getDevices($gets = [], $page = 1, $pagesize = 10) {
//		$list = DbDevice::getInstance()->getAllDevices();
//		print_r($list);
		$deives = Lib\Robot::$table;
		$res = [];
		foreach($deives as $k => $v){
		    $res[$k] = $v;
        }
//		foreach ($list as $k => $task) {
//			$tmp = Lib\Robot::$table->get($task["c_devicesn"]);
//			if (!empty($tmp)) {
//				$list[$k]["lasttime"] = $tmp["lasttime"];
//				$list[$k]["isconnect"] = 1;
//			} else {
//				$list[$k]["isconnect"] = 0;
//			}
//		}

        return $res;
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
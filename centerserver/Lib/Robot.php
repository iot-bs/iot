<?php
/**
 * 中心服中的任务分发
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-22
 * Time: 下午3:27
 */

namespace Lib;
use model\Device;
use Lib\Monitor;
class Robot {

	static public $table;
	static public $groupTable;
	static public $aTable;
	static private $devicesns;
	static public $tableAgent;

	static private $column = [
		"fd" => [\swoole_table::TYPE_INT, 8],
		"lasttime" => [\swoole_table::TYPE_STRING, 16],
	];
	static private $aColumn = [
		"devicesn" => [\swoole_table::TYPE_STRING, 15],
	];

	public static function init() {
		echo "Lib ------ Robot ----------init\n" . PHP_EOL;
		self::$table = new \swoole_table(ROBOT_MAX * 2);
		foreach (self::$column as $key => $v) {
			self::$table->column($key, $v[0], $v[1]);
		}
		self::$table->create();

		self::$aTable = new \swoole_table(ROBOT_MAX * 2);
		foreach (self::$aColumn as $key => $v) {
			self::$aTable->column($key, $v[0], $v[1]);
		}
		self::$aTable->create();
		self::loadAgents();
	}

	/**
	 * 载入分组代理信息
	 * @return bool
	 */
	public static function loadAgents() {
		echo "Lib ------ Robot ----------loadAgents\n" . PHP_EOL;
		self::$tableAgent = Device::getInstance();
		$agents = self::$tableAgent->getAllDevices(['c_status = 0']);
		if (empty($agents)) {
			return false;
		}
		foreach ($agents as $agent) {
			if (count(self::$aTable) > ROBOT_MAX) {
				print_r("loadAgents fail ,because robot size max");
				Flog::log("loadAgents fail ,because robot size Max");
				return true;
			}
			self::$aTable->set($agent["c_deviceid"], [
				"devicesn" => $agent["c_devicesn"],
			]);
		}
		return true;
	}
	public static function stopAgent($id) {
		echo "Lib ------ Robot ----------stopAgent" . PHP_EOL;
		$res = self::$aTable->del($id);
		$res = self::$tableAgent->getOneDevice($id);
		$devicesn = $res['c_devicesn'];
		if (self::$table->exist($devicesn)) {
			$client = new Client($devicesn);
			// $client->call("close", []);
			$client->close();
			if (self::$table->del($devicesn)) {
				$res1 = true;
			} else {
				$res1 = false;
			}
		}
		{
			$res1 = true;
		}
		print_r('del' . $res1);
		if ($res && $res1) {
			return true;
		}

		return false;
	}
	/**
	 * @param    [type]      $id [add id]
	 * @return   [type]          [description]
	 */
	public static function startAgent($id) {
		echo "Lib ------ Robot ----------startAgent\n" . PHP_EOL;
		$agent = self::$tableAgent->getOneDevice($id);
		$res = self::$aTable->set($agent["c_deviceid"], [
			"devicesn" => $agent["c_devicesn"],
		]);
		if ($res) {
			return true;
		}
		return false;
	}
	/**
	 * del agent device
	 * del agent device
	 * @param    [type]      $id [del id]
	 * @return   [type]          [boolean]
	 */
	public static function delAgent($id) {
		echo "Lib ------ Robot ----------delAgent" . PHP_EOL;
		$res = self::$aTable->del($id);
		$res = self::$tableAgent->getOneDevice($id);
		print_r($res['c_devicesn']);
		$devicesn = $res['c_devicesn'];
		if (self::$table->exist($devicesn)) {
			$client = new Client($devicesn);
			$client->close();
			if (self::$table->del($devicesn)) {
				$res1 = true;
			} else {
				$res1 = false;
			}
		}else{
            $res1 = true;
        }
        if($res1){
		    return true;
        }
        return false;


	}

	/**
	 * 注册服务
	 * @param $fd
	 * @param $devicesn
	 * @return bool
	 */
	public static function register($fd, $devicesn) {
		echo "Lib ------ Robot ----------register\n" . PHP_EOL;
		$id = self::$tableAgent->getOneDevice($devicesn,'c_devicesn');
		if (empty($id)) {
			return false;
		}
		if (self::$aTable->exist($id['c_deviceid'])) {
			if (self::$table->exist($devicesn)) {
				$client = new Client($devicesn);
				$client->call("close", []);
			}
			if (self::$table->set($devicesn, ['fd' => $fd, "lasttime" => time()])) {
			    echo "set";
				return true;
			}
		}
        echo "endLib ------ Robot ----------register\n" . PHP_EOL;
		return false;

	}

	/**
	 * 注销服务
	 * @param $fd
	 * @return mixed
	 */
	public static function unRegister($fd) {
		echo "Lib ------ Robot ----------unRegister\n" . PHP_EOL;
		foreach (self::$table as $devicesn => $value) {
			echo 'fd == '; print_r($fd);
			if ($value["fd"] == $fd) {
                print_r($devicesn);
                print_r($fd);
				Monitor::unRegister($devicesn);
				return self::$table->del($devicesn);
			}
		}
		return true;
	}

	private static function loadIps() {
		echo "Lib ------ Robot ----------loadIps\n" . PHP_EOL;
		foreach (self::$table as $k => $v) {
			self::$devicesns[$k] = $v;
		}
	}

}
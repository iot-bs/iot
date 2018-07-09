<?php
/**
 * 监控状态 标题
 * @Author   liuxiaodong
 * @DateTime 2018-04-10
 */

namespace Lib;
use model\Device;
use model\Monitor as DbMonitor;
class Monitor {
	static public $table;
	//数据库表
    static public $tableMonitor;
    static public $tableDevice;
	static private $column = [
		"c_devicesn" => [\swoole_table::TYPE_STRING, 12], //设备编号
		"c_voltage" => [\swoole_table::TYPE_STRING, 400], //设备电压
		"c_current" => [\swoole_table::TYPE_STRING, 400], //设备电流
		"c_temp" => [\swoole_table::TYPE_STRING, 200], //设备温度
		"c_lng" => [\swoole_table::TYPE_STRING, 100], //设备维度
		"c_lat" => [\swoole_table::TYPE_STRING, 100], //设备經度
		"c_device_request" => [\swoole_table::TYPE_STRING, 5], //设备请求方式
		"c_relay" => [\swoole_table::TYPE_STRING, 200], //设备继电器
		"c_connect_type" => [\swoole_table::TYPE_STRING, 5], //设备链接方式
	];

	/**
	 * 创建配置表
	 */
	public static function init() {
		echo "Lib ------ Monitor ----------init\n" . PHP_EOL;
		self::$table = new \swoole_table(MONITOR_SIZE * 2);
		foreach (self::$column as $key => $v) {
			self::$table->column($key, $v[0], $v[1]);
		}
		self::$table->create();
		self::$tableMonitor = \model\Monitor::getInstance();
		self::$tableDevice = \model\Device::getInstance();
	}

    /**
     *
     * @param $data
     * @return bool
     */
    public static function updateMonitor($data){
//        echo "Lib ------ Monitor ----------updateMonitor\n" . PHP_EOL;
        $table['c_devicesn'] = $data['DeviceSn'];
        $table['c_voltage'] = serialize($data['Vdc']);
        $table['c_current'] = serialize($data['Current']);
        $table['c_temp'] = $data['Temp'];
        $table['c_lng'] = $data['Lng'];
        $table['c_lng'] = $data['Lng'];
        $table['c_lat'] = $data['Lat'];
        $table['c_device_request'] = $data['RequestControl'];
        $table['c_relay'] = serialize($data['Relay']);
        $table['c_connect_type'] = $data['ConnectType'];
        $table['create_time'] = time();
        self::$tableMonitor->insertMonitor($table);
        if(!self::$table->set($data['DeviceSn'],$table)){
            return false;
        }
        return true;

    }
    public static function unRegister($devicesn) {
        echo "Lib ------ Robot ----------unRegister\n" . PHP_EOL;
        foreach (self::$table as $sn => $value) {
            if ($sn == $devicesn) {
                return self::$table->del($devicesn);
            }
        }
    }

}
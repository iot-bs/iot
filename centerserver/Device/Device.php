<?php
namespace Device;
use Lib\Robot;
use Lib\Util;
use Lib\Monitor;
use model\Warning;
use model\SafeLimit;
class Device
{
    static public $tableSafeLimit;
    static public $tableWarning;
    static public $tableDevice;
    public function __construct()
    {
        self::$tableSafeLimit = \model\SafeLimit::getInstance();
        self::$tableWarning  = \model\Warning::getInstance();
        self::$tableDevice = \model\Device::getInstance();

    }

    //初始连接
	public function initConnect($data) {
		echo "Device ------ Device ----------initConnect\n" . PHP_EOL;
		if (Robot::register($data['fd'], $data['DeviceSn']) && Monitor::updateMonitor($data)) {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);

		} else {
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		echo "endDevice ------ Device ----------initConnect\n" . PHP_EOL;
	}
	//心跳设置状态
	public function heartbeatSet($data) {
        if(!\Table\SafeLimit::updateSafeLimit($data)){
            return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
        }
        return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);
	}
	//预订单状态检查
	public function preOrderCheck($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
	}
	//设备状态检查
	public function deviceCheck($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);		
	}
	//断开连接
	public function closeConnect($data) {
		if(!Robot::unRegister($data['fd'])){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);			
		}
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);	
	}
	//客户端设置状态$
	public function deviceSet($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']); 
	}
	//断开电源
	public function closePower($data) {
		if(!Monitor::updateMonitor($data)){
			return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
		}
		return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']); 
	}
    //电流设置状态
    public function currentSet($data) {
        self::$tableSafeLimit->updateSafeLimit($data);
        if(!\Table\SafeLimit::updateSafeLimit($data))
        {
            return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
        }
        return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);
    }
    //电压设置状态
    public function voltageSet($data) {
        self::$tableSafeLimit->updateSafeLimit($data);
        if(!\Table\SafeLimit::updateSafeLimit($data))
        {
            return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
        }
        return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);
    }
    //温度设置状态
    public function tempSet($data) {
        echo "Device ------ Device ----------tempSet\n" . PHP_EOL;
        self::$tableSafeLimit->updateSafeLimit($data);
        if(!\Table\SafeLimit::updateSafeLimit($data)){
            return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
        }
        return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);
    }
    //获取设备安全阈值
    public function getSafeLimit($data){
	    echo "-----------------anquan ceshio yuzhi ---".PHP_EOL;
        $res = self::$tableSafeLimit->getSafeLimit($data['DeviceSn'],'c_devicesn');
         $msg = Util::msg('13',['DeviceSn' =>$data['DeviceSn'],'VdcConArray' => unserialize($res['c_vdccon']),'TempConArray' => unserialize($res['c_tempcon']),'CurrentConArray' => unserialize($res['c_currentcon']),'Heartbeat' => $res['c_heartbeat'],'RequestStatus' => '1']);
         return $msg;

    }
    //安全警报
    public function warnSet($data){
	    $res = self::$tableWarning->insertWarnData($data);
	    if($res){
            return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '1']);
        }
        return Util::msg('1',['DeviceSn' => $data['DeviceSn'],'RequestStatus' => '0']);
    }
}
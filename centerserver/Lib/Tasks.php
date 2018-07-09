<?php
/**
 * 管理需要处理的任务
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-19
 * Time: 下午4:33
 */

namespace Lib;
use model\Device;
use model\OrderStatus;
class Tasks
{
    static public $table;

    static private $column = [
        "No" => [\swoole_table::TYPE_STRING, 12],//继电器标号（1,2,3）
        "Value" => [\swoole_table::TYPE_STRING, 12],//开关状态 （1,0）
        "Type" => [\swoole_table::TYPE_STRING, 12]//执行的类型 单次 全部（'one','all'）
    ];

    /**
     * 创建配置表
     */
    public static function init()
    {
        echo "Lib ------ Tasks ----------init\n".PHP_EOL;
        self::$table = new \swoole_table(DEVICES_SIZE*2);
        foreach (self::$column as $key => $v) {
            self::$table->column($key, $v[0], $v[1]);
        }
        self::$table->create();
    }
    public static function aftertest($server)
    {

        $server->after(6000, function () use ($server) {
            echo "aftertest1".PHP_EOL;
            $server->after(6000, function () use ($server) {
                echo "aftertest2".PHP_EOL;
                $server->after(6000, function () use ($server) {
                    echo "aftertest3".PHP_EOL;
                });
            });

        });



    }

    /**
     * @param $server swoole
     * 手动心跳处理
     */
    public static function heartBeat($server)
    {
        echo " heart beat che k".PHP_EOL;
        $hearts = $server->heartbeat(false);
        if($hearts)
        {
            foreach ($hearts as $k => $v)
            {
                Robot::unRegister($v);
            }
        }
        print_r($hearts);
    }
    /**
     * 周期检查任务 判断订单状态是否正常
     * 1：
     *   检查在线设备的继电器状态，检查为开的情况下就去查询订单状态表是否有存在的 订单 有就开着，没有就关闭
     *   继电器1位总开关，如果继电器未开 其他都开着 则将其他都关闭
     * 2：
     * 检查订单状态表，所有status为1 的情况 看看设备是否开着没有就开着
     * @param $server
     */
    public static function checkTasks($server)
    {
        echo "task check".PHP_EOL;
        $res = OrderStatus::getInstance()->getAllStatus(['c_status > 0']);
        //worker 2进程 执行两个异步并发任务检查
        CenterServer::$_server->task($res,'checkDeviceStop');
        CenterServer::$_server->task($res,'checkOrderStatus');
    }
    //检查设备状态 是否正常（开着的情况下是否有订单存在）
    public static function checkDeviceStop($nul)
    {
        echo "device status check stop".PHP_EOL;
        $monitors = Monitor::$table;
        $devices = [];
        foreach($monitors as $k => $v)
        {
            $device = Device::getInstance()->getOneDevice($k,'c_devicesn');
            if($device['c_type'] == '3')
            {
                continue;
            }
            $relay = unserialize($v['c_relay']);
            if(!self::safeCheck($relay))
            {
                self::$table->set($k,['No' => 'all','Value' => 'all' ,'Type' => 'all']);
            }
            {
                //判断继电器1开着的情况下 是否有订单存在 没有就关闭
                if($relay['2'] == '1')
                {
                    $res  = OrderStatus::getInstance()->getAllStatus(['c_status = 1',"c_device_id = {$device['c_deviceid']}","c_type = 1"]);
                    if(empty($res))
                    {
                        self::$table->set($k,['No' => '2','Value' => '0' ,'Type' => 'one']);
                    }
                }
                //判断继电器2开着的情况下 是否有订单存在 没有就关闭
                if($relay['3'] == '1')
                {
                    $res  = OrderStatus::getInstance()->getAllStatus(['c_status = 1',"c_device_id = {$device['c_deviceid']}","c_type = 2"]);
                    if(empty($res))
                    {
                        self::$table->set($k,['No' => '3','Value' => '0' ,'Type' => 'one']);
                    }
                }

            }
        }
        return true;
    }
    //检查继电器状态是否正常
    public static function safeCheck($relay)
    {
        if($relay['1'] == '0')
        {
            if($relay['2'] == '1' || $relay['3'] == '1')
            {
                return false;
            }
        }else if($relay['1'] == '1')
        {
            if($relay['2'] == '0' && $relay['3'] == '0')
            {
                return false;
            }
        }

        return true;
    }
    //检查订单存在的情况下 继电器是否开着
    public static function checkOrderStatus($nul)
    {
        echo "order check start".PHP_EOL;
        $res = OrderStatus::getInstance()->getAllStatus(['c_status = 1']);
        foreach ($res  as $k => $v)
        {
            $device = Device::getInstance()->getOneDevice($v['c_device_id']);
            if(self::orderTimeCheck($v,$device['c_deviceid']))
            {
                $monitor = Monitor::$table->get($device['c_devicesn']);
                $relay = unserialize($monitor['c_relay']);
                //判断继电器2 开着没有
                if($v['c_type'] == '1')
                {
                    if($relay['2'] != '1')
                    {
                        self::$table->set($device['c_devicesn'],['No' => '2','Value' => '1' ,'Type' => 'one']);
                    }
                }
                //判断继电器3开着没有
                if($v['c_type'] == '2')
                {
                    if($relay['3'] != '1')
                    {
                        self::$table->set($device['c_devicesn'],['No' => '3','Value' => '1' ,'Type' => 'one']);
                    }
                }
            }
        }
        return true;
    }
    //订单时间状态检查
    public static function orderTimeCheck($order , $deviceid)
    {
          if(time() > ($order['c_start_time']+3600*$order['c_charge_time']))
          {
             OrderStatus::getInstance()->updateOrderStatus($order['c_id'],['c_status' => '2']);
             return false;
          }
          return true;
    }

    /**
     * 继电器开关控制
     * @param $data
     * @return bool
     */
    public static function updateRelay($data){
        echo 'LIB------------------------Tasks ----------------updaterelay'.PHP_EOL;
        $devicesn = $data['c_devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('3',['DeviceSn' => $devicesn,'Relay' => $data['c_relay']]);
        $client = new Client($devicesn);
        $client->control($call);
        //判断设备继电器返回的结果，从内存中读取结果进行判断
        sleep(2);
        $res = \Table\SafeLimit::$table->get($devicesn);
        try{
            if(!empty($res))
            {
                $res = unserialize($res['safe_limit']);
                if($res['RequestControl'] == '7' && $res['ControlStatus'] == '1')
                {
                    $relay = $res['Relay'];
                    foreach ($data['c_relay'] as $k => $v)
                    {
                        if($relay[$k] == $v){
                            $ret = false;
                        }
                        {
                            $ret = true;
                        }
                    }
                }
            }else
            {
                return false;
            }
            return $ret;
        }
        catch (Exception $e){
            return false;
        }

   }

    /**
     * @param $data
     * 控制wifi连接
     * @return bool
     */
    public static function contype($data){
         $devicesn = $data['c_devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('9',['DeviceSn' => $devicesn,'WifiCon' => ['Acount' => $data['username'],'Pass' => $data['passwd']]]);
        $client = new Client($devicesn);
        $client->control($call);                
        return true;       
    }
    public static function execTask($data)
    {
            self::updateRelay($data);
            self::$table->del($data['c_devicesn']);
            return true;
    }
    /**
     * worker进程id 为0 的任务执行
     */
    public static function doTasks()
    {
        foreach (self::$table as $k => $v)
        {
            $data['c_devicesn'] = $k;
            if($v['Type'] == 'all')//全部关闭
            {
                $data['c_relay'] = ['2' => '0'];
                self::execTask($data);
                $data['c_relay'] = ['3' => '0'];
                self::execTask($data);
                //延迟30秒关闭继电器1
                $data['c_devicesn'] = $k;
                $data['c_relay'] = ['1' => '0'];
                self::execTask($data);
//                CenterServer::$_server->after(30000,function()use($k){
//                    $data['c_devicesn'] = $k;
//                    $data['c_relay'] = ['1' => '0'];
//                    self::execTask($data);
//                });
            }
            else if($v['Type'] == 'on')//全部开启
            {
                $data['c_devicesn'] = $k;
                $data['c_relay'] = ['1' => '1'];
                self::execTask($data);
                $data['c_relay'] = ['2' => '1'];
                self::execTask($data);
                $data['c_relay'] = ['3' => '1'];
                self::execTask($data);
//                    CenterServer::$_server->after(30000,function()use($k){
//                        $data['c_relay'] = ['2' => '1'];
//                        self::execTask($data);
//                        $data['c_relay'] = ['3' => '1'];
//                        self::execTask($data);
//                        //延迟30秒开启继电器1
//                    });
            }
            else if($v['Type'] == 'one')
            {
                if($v['No'] == '1')
                {
                    $data['c_relay'] = [$v['No'] => $v['Value']];
                    self::execTask($data);
                }else
                {
                    //判断继电器1是否开着，没有开就延时1分钟进行其他操作
                    $device = Monitor::$table->get($k);
                    if(!empty($device))
                    {
                        $relay = unserialize($device['c_relay']);
                        if($relay['1'] == '0')
                        {
                            $data['c_relay'] = ['1' => '1'];
                            self::execTask($data);
                            CenterServer::$_server->after(60000,function()use($k,$v){
                                $data['c_devicesn'] = $k;
                                $data['c_relay'] = [$v['No'] => $v['Value']];
                                self::execTask($data);
                            });
                        }
                        $data['c_devicesn'] = $k;
                        $data['c_relay'] = [$v['No'] => $v['Value']];
                        self::execTask($data);

                    }
                }

//                CenterServer::$_server->task($data,'execTask');
            }
        }
        return true;

    }

}
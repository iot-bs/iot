<?php
/**
 * 管理需要处理的任务
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-19
 * Time: 下午4:33
 */

namespace Lib;

class Tasks
{
    static public $table;

    static private $column = [
        "minute" => [\swoole_table::TYPE_STRING, 12],
        "sec" => [\swoole_table::TYPE_STRING, 12],
        "id" => [\swoole_table::TYPE_STRING, 20],
        "runid" => [\swoole_table::TYPE_STRING, 20],
        "runStatus" => [\swoole_table::TYPE_STRING, 2],
    ];

    /**
     * 创建配置表
     */
    public static function init()
    {
        echo "Lib ------ Tasks ----------init\n".PHP_EOL;
        self::$table = new \swoole_table(TASKS_SIZE*2);
        foreach (self::$column as $key => $v) {
            self::$table->column($key, $v[0], $v[1]);
        }
        self::$table->create();
    }
    public static function updateRelay($data){
        print_r('LIB------------------------Tasks ----------------updaterelay').PHP_EOL;
        $devicesn = $data['c_devicesn'];
        $fd = Robot::$table->get($devicesn);
        if(!$fd){
            return false;
        }
        $call = Util::msg('3',['DeviceSn' => $devicesn,'Relay' => $data['c_relay']]);
        $client = new Client($devicesn);
        $client->control($call);                
        $res = Monitor::$table->get($devicesn);
        $relay = unserialize($res['c_relay']);
        foreach ($data['c_relay'] as $k => $v) {
            # code...
            if($relay[$k] == $v){
                $ret = false;
            }{
                $ret = true;
            }
        }
        if(!$ret){
            return false;
        }
        return true;
    }
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

}
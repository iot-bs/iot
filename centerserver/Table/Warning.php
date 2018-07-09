<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/4/26 0026
 * Time: 10:45
 */


namespace Table;
use model\Warning as DbWarning;
use Device\Wx;
class Warning {
    const cleanTime = 60;//处理超时心跳连接
    static public $table;

    static private $column = [
        "warning" => [\swoole_table::TYPE_STRING, 800], //警报状态
        "lasttime" => [\swoole_table::TYPE_STRING, 16],
    ];

    /**
     * 创建配置表
     */
    public static function init() {
        echo "Lib ------ Warning ----------init\n" . PHP_EOL;
        self::$table = new \swoole_table(MONITOR_SIZE * 2);
        foreach (self::$column as $key => $v) {
            self::$table->column($key, $v[0], $v[1]);
        }
        self::$table->create();
    }
    public static function updateWarning($data){
        echo "table ------ Warning ----------updatewarning\n" . PHP_EOL;
        $devicesn = $data['DeviceSn'];
        $warnData = serialize($data);
        DbWarning::getInstance()->insertWarnData($data);
        if(!self::$table->set($devicesn,['warning' => $warnData ,'lasttime' => time()])){
            return false;
        }
        return true;

    }
    public static function cleanWarning()
    {
        echo "clean warning ----------------".PHP_EOL;
        foreach (self::$table as $k => $v) {
            if((time() - $v['lasttime']) > self::cleanTime)
            {
                $msg['first'] = '设备状态异常，请进入后台查看设备状况并采取安全措施';
                $msg['keyword1'] = $k;
                $msg['keyword2'] = $k;
                $msg['keyword3'] = $v['warning'];
                $msg['keyword4'] = time();
                $msg['remark'] = '详情请关注后台监控状态';
                $res = Wx::getInstance()->sendNotice(WARNID,$msg);
                print_r($res);
                self::$table->del($k);
            }
        }
    }

}
<?php
/**
 * @Author   liuxiaodong
 * @DateTime 2018-03-01
 * @define  the file is the entrance of the system
 */
if (!extension_loaded('swoole')) {
	echo "Please install the Swoole expansion\n";
	exit();
}
if (!extension_loaded('pcre')) {
	echo "Please install the pcre expansion\n";
	exit();
}
define('SERVICE', true);
define('WEBPATH', __DIR__);
define('SWOOLE_SERVER', true);
date_default_timezone_set("Asia/Shanghai");
function getRunPath() {
	$path = Phar::running(false);
	if (empty($path)) {
		return __DIR__;
	} else {
		return dirname($path) . "/../crontab_log";
	}

}

const LOAD_SIZE = 8192; //最多载入任务数量
const TASKS_SIZE = 1024; //同时运行任务最大数量
const MONITOR_SIZE = 1024; //同时运行监控任务最大数量
const ROBOT_MAX = 128; //同时挂载worker数量
const WORKER_NUM = 4; //worker进程数量
const TASK_NUM = 4; //task进程数量

define("CENTRE_PORT", 8901);
define('DEBUG', 'on');
define("CENTER_HOST", "127.0.0.1");
$env = 'dev';
define('ENV_NAME', $env);
define('PUBLIC_PATH', '/website/iot/centerserver/');
/**
 * require the swoole_framework
 */
require_once PUBLIC_PATH . 'libs/lib_config.php';
require_once PUBLIC_PATH . 'vendor/autoload.php';
think\Db::setConfig([
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '127.0.0.1',
    // 数据库名
    'database'        => 'do_charge',
    // 用户名
    'username'        => 'root',
    // 密码
    'password'        => 'roadforhacker',
    // 端口
    'hostport'        => '3306',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => 't_',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Query类
    'query'           => '\\think\\db\\Query',
    'break_reconnect' => true,
]);
/**
 * require the thinkphp
 */
//require PUBLIC_PATH . '/thinkphp/base.php';
//think\Container::get('app')->run()->send();

Swoole::$php->config->setPath(__DIR__ . '/configs/' . ENV_NAME); //共有配置
Swoole::$php->config->setPath(__DIR__ . '/configs'); //共有配置
Swoole\Loader::addNameSpace('App', __DIR__ . '/App');
Swoole\Loader::addNameSpace('Lib', __DIR__ . '/Lib');
Swoole\Loader::addNameSpace('Device', __DIR__ . '/Device');
Swoole\Loader::addNameSpace('Table', __DIR__ . '/Table');
Swoole\Loader::addNameSpace('model', __DIR__ . '/model');

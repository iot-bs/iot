<?php
/**
 * 中心服服务
 * Created by PhpStorm.
 * User: liuzhiming
 * Date: 16-8-19
 * Time: 下午3:56
 */

namespace Lib;

use Device\Wx;
use Device;
use Swoole;
use Lib\Robot;
class CenterServer extends Swoole\Protocol\SOAServer {
	/**
	 * @var Swoole\Network\Server
	 */
	public static $_server;

	const LOAD_TASKS = 0; //载入任务tasks进程
	const GET_TASKS = 1; //获取到期task进程
	const EXEC_TASKS = 2; //执行task 周期检查对 订单状态进行检查，判断继电器开关是否正常
	const MANAGER_TASKS = 3; //管理task状态
    const CLEANWARNING =5;//清理警告队列
    const PUSHMSG = 6;//定时推送微信公众警告任务

	function onWorkerStart($server, $worker_id) {

		echo "Lib ------ CenterServer ----------onWorkerStart" . PHP_EOL;
		Swoole::$php->db->connect();
		//判断是否是worker进程
        if (!$server->taskworker) {
            //为进程3设置检查周期任务
            if($worker_id == self::EXEC_TASKS)
            {
                //准点载入任务
                $server->after((60 - date("s")) * 1000, function () use ($server) {
                    $server->tick(60000, function () use ($server) {
                        Tasks::checkTasks($server);
                    });
                });
            }
            //将task 任务载入 worker 为0 的进程 进行任务执行
            if($worker_id == self::LOAD_TASKS)
            {
                $server->tick(500, function () use ($server) {
                    Tasks::doTasks();
                });
            }
        }else{
            // 清理警告队列
            if($worker_id == self::CLEANWARNING)
            {
                //准点载入任务
                $server->after((60 - date("s")) * 1000, function () use ($server) {
                    $server->tick(60000, function () use ($server) {
                        \Table\Warning::cleanWarning();
                    });
                });
            }
            //公众号定时推送
//            if($worker_id == self::PUSHMSG)
//            {
//                //准点载入任务
//                $server->after((60 - date("s")) * 1000, function () use ($server) {
//
//                    $msg['first'] = 'centerserver';
//                    $msg['keyword1'] = 'centerserver';
//                    $msg['keyword2'] = '紧急充电用户';
//                    $msg['keyword3'] = 'centerserver';
//                    $msg['keyword4'] = 'centerserver';
//                    $msg['remark'] = 'centerserver';
//                    Wx::getInstance()->sendNotice(WARNID,$msg);
//                });
//            }
        }
	}
    //投送异步任务
	public function onTask($serv, $task_id, $src_worker_id, $data) {
//            echo "Lib ------ CenterServer -----{$task_id}--{$src_worker_id}---onTask\n" . PHP_EOL;
            $mehod = $data['func'];
            $flag = Tasks::$mehod($data['data']);
            return $flag;
	}

	public function onFinish($serv, $task_id, $data ){
//		echo "Lib ------ CenterServer ----------onFinish\n" . PHP_EOL;
	}

	public function onPipeMessage($serv, $src_worker_id, $data) {
		echo "Lib ------ CenterServer ----------onPipeMessage\n" . PHP_EOL;
	}

	public function call($request, $header) {
//		echo "Lib ------ CenterServer ----------call\n" . PHP_EOL;
//		print_r($request);
		 print_r($header);
        if(isset($request['RequestControl'])){
            if(in_array($request['RequestControl'],['8','9','10','11','12','2'])){
                return Device\RequestCate::distributeRquest($request,$request['RequestControl']);
            }
        }

		$request['fd'] = $header['fd'];
//        self::$_server->protect($header['fd'],true);
		$res = Device\Split::isDevice($request);
//		print_r($res);
		if ($res['key'] > 0) {
			if($res['key'] != 9){

				return Util::msg('1',['DeviceSn' => $request['DeviceSn'],'RequestStatus' => '0']);
			}
			return Device\RequestCate::requestControl($res);
		}
//		echo "------- page fd";
//		print_r(Robot::$table->get('127.0.0.1'));
//		//初始化日志
//		Flog::startLog($request['call']);
//		Flog::log("call:" . $request['call'] . ",params:" . json_encode($request['params']));
//		if ($request['call'] == 'register') {
//			if (Robot::register($header['fd'], self::$clientEnv['_socket']['remote_ip'])) {
//				return array('errno' => 0, 'data' => Util::errCodeMsg(0, "注册成功"));
//			} else {
//				self::$_server->close($request['fd']);
//				return ['errno' => 8010, 'data' => Util::errCodeMsg(8010, "设备未注册，没有权限连接服务器")];
//			}
//		}
		$ret = parent::call($request, $header);
//		Flog::log($ret);
//		Flog::endLog();
//		Flog::flush();
//        print_r($ret);
		return $ret;
	}

	public function onClose($serv, $fd, $from_id) {
		echo "Lib ------ CenterServer ----------onClose\n" . PHP_EOL;
//		parent::onClose($serv, $fd, $from_id);
        print_r($fd);
		Robot::unRegister($fd);
	}
}
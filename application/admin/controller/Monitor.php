<?php
/**
 * User: liuxiaodong
 * Date: 2018/3/5
 * Time: 18:43
 */

namespace app\admin\controller;
use app\service\Service;
use function Couchbase\defaultDecoder;

class Monitor extends Base {
	public $monitor;
	public $type;
	public $validate;
	public function initialize() {
		$this->monitor = model("Monitor");
		$this->type = config('device.deviceType');
		$this->validate = validate('Device');
		$this->assign('type', $this->type);
	}
	/**
	 * 首页渲染
	 */
	public function index() {


		$list = Service::getInstance()->call("Device::test")->getResult(10);
        print_r("monitor <br><br><br>");
		var_dump($list['Monitor']);
        print_r("<br><br><br>task ");
        var_dump($list['Task']);
        print_r("<br><br><br>robot ");
        var_dump($list['Robot']);
        print_r("<br><br><br>safe limit");
        var_dump($list['SafeLimit']);
        print_r("<br><br><br>heartbeat");
        var_dump($list['heartbeat']);
        print_r("<br><br><br>warning");
        var_dump($list['warning']);


	}

    /**
     * 封装rpc返回的monitor状态
     */
	public function getMonitors($where = [])
    {
        $monitors = Service::getInstance()->call("Monitor::getMonitors")->getResult(10);
        $deviceStatus = $monitors['deviceStatus'];
        $monitorStatus = $monitors['monitorStatus'];
        $res = model('Device')->where($where)->select()->toArray();

        foreach ($res as $k => $v)
        {
            if (array_key_exists($v['c_devicesn'], $deviceStatus)) {
                $res[$k]["lasttime"] = $deviceStatus[$v['c_devicesn']]["lasttime"];
                $res[$k]["isconnect"] = 1;
            } else {
                $res[$k]["isconnect"] = 0;
            }
            if (array_key_exists($v['c_devicesn'], $monitorStatus)) {
                $res[$k]['monitor'] = $monitorStatus[$v['c_devicesn']];
            } else {
                $res[$k]['monitor'] = [];
            }

        }
        return $res;
    }
	/**
	 * @Author   liuxiaodong
	 * @DateTime 2018-04-11
	 * @return   [type]      [description]
	 */
	public function status() {
	    $so = input('get.so');
	    $where = [];
	    if(!empty($so))
        {
            $where[] = ['c_devicesn','like',"%".$so."%"];
        }
        $res = $this->getMonitors($where);
		$list = [];
		foreach ($res as $k => $v) {
			# code...
            $heartbeat = model('SafeLimit')->where('c_devicesn',$v['c_devicesn'])->find();
			if($v['monitor']){
				$v['monitor']['c_voltage'] = unserialize($v['monitor']['c_voltage']);
				$v['monitor']['c_current'] = unserialize($v['monitor']['c_current']);
				$v['monitor']['c_relay'] = unserialize($v['monitor']['c_relay']);
				$list[$k] = $v['monitor'];
				$list[$k]['c_deviceid'] = $v['c_deviceid'];
				$list[$k]['c_type'] = $this->type[$v['c_type']];
//				$list[$k]['map'] = \map\Map::Staticimage($v['c_lng'].','.$v['c_lat']);
				$list[$k]['lng'] = $v['monitor']['c_lng'];
				$list[$k]['lat'] = $v['monitor']['c_lat'];
				$list[$k]['isconnect'] = 1;
				$list[$k]['heartbeat'] = $heartbeat['c_heartbeat'];
				// $list[$k]['map'] = \map\Map::Staticimage('106.67923744596,28.87613983528');
			}
			else{
				$list[$k]['c_deviceid'] = $v['c_deviceid'];
				$list[$k]['c_devicesn'] = $v['c_devicesn'];
				$list[$k]['c_type'] = $this->type[$v['c_type']];
				$list[$k]['isconnect'] = 0;

			}
		}
//		 var_dump($list);exit;
		return $this->fetch('', [
			'title' => '设备列表',
			'list' => $list,
		]);
	}
	public function map()
    {
        if(request()->isGet())
        {
            $data = input('get.');
            if(empty($data['id'])){
                $this->error('缺少id');
            }
            if(empty($data['lng']) || empty($data['lat']))
            {
                $this->error('缺少经纬度');
            }
            $url = sprintf(config('map.convert_map_url'),$data['lng'],$data['lat']);
            $res = json_decode(doCurl($url),true);
            if(!$res['status'] == 0)
            {
                $this->error('非法错误');

            }
//            print_r("x =".$res['result'][0]['x']);
//            print_r("y =".$res['result'][0]['y']);
            $device = model('Device')->getDeviceById($data['id']);
             $point = [
            "lat" => $res['result'][0]['y'],
            "lng" => $res['result'][0]['x']
             ];
                return $this->fetch('', [
                 'title' => $device['c_name'],
                 'point' => $point
                ]);
        }
        $this->error('非法访问');

    }

    /**
     * @return mixed
     * 数据展示列表
     */
    public  function datashow(){
        $list = model('Device')->select()->toArray();
        return $this->fetch('', [
            'title' => '数据展示',
            'list' => $list,
        ]);
    }

    /**
     * 返回电流的数据渲染图
     * @return mixed
     */
    public function current(){
        if(request()->isGet()){
            $this->assign('nos',config('devcon.currentCon'));
        	$this->dataDeal('c_current','电流');
            return $this->fetch();
        }
        $this->error('数据错误');
    }
    /**
     * 渲染电压数据图
     * @return mixed
     */
    public function voltage(){
        if(request()->isGet()){
            $this->assign('nos' ,config('devcon.voltageCon'));
            $this->dataDeal('c_voltage','电压');
            return $this->fetch();
        }
        $this->error('数据错误');
    }
    public function dataDeal($dataType,$name){
        $no = input('get.no')?input('get.no'):1;
        $type=input('get.type')?input('get.type'):'day';
        $data = [];
        $date = [];
        $current= [];
        $devicesn = input('get.devicesn');
        $list = $this->monitor->getMonitor($devicesn,$type)->toArray();
        foreach ($list as $k=>$v){
            $v['c_voltage'] = unserialize($v['c_voltage']);
            $v['c_current'] = unserialize($v['c_current']);
            $date[$k] = $v['create_time'];
            $data[$k] = $v[$dataType][$no-1]['Value'];
        }
        if($type == "day")
            $content ="今天";
        elseif($type == "week")
            $content="本周";
        else
            $content="本月";
        $this->assign([
                'title'=>$name.'数据渲染图',
                'content' =>$content,
                'data' => json_encode($data),
                'date' => json_encode($date),
                'no' => $no,
                'type'=>$type,
                'devicesn' => $devicesn,
        ]);

    }
    /**
     * 渲染温度的数据图
     * @return mixed
     */
    public function temp(){
        if(request()->isGet()){
            $type=input('get.type')?input('get.type'):'day';
            $data = [];
            $date = [];
            $devicesn = input('get.devicesn');
            $list = $this->monitor->getMonitor($devicesn,$type);
            foreach ($list as $k=>$v){
                $date[$k] = $v['create_time'];
                $data[$k] = $v['c_temp'];
            }
            if($type == "day")
                $content ="今天";
            elseif($type == "week")
                $content="本周";
            else
                $content="本月";
            return $this->fetch('',[
                'title'=>'温度数据渲染图',
                'content' =>$content,
                'data' => json_encode($data),
                'date' => json_encode($date),
                'type'=>$type,
                'devicesn' => $devicesn,
            ]);
        }
        $this->error('数据错误');
    }

	public function split($data)
    {
		foreach ($data as $k => $v)
		{
					# code...
			
		}		
	}

    /**
     * 数据监控警报模块
     */
    public function warning()
    {
        $list = model('Device')->select();
        foreach ($list as $v){
           $res = db('Warning')->where('c_devicesn',$v['c_devicesn'])->find();
           $relay = db('Relay')->where('c_devicesn',$v['c_devicesn'])->find();
           if(empty($res)){
                $v['warning'] = 0;
            }else{
                $v['warning'] = 1;
            }
            if(empty($relay)){
                $v['relay'] = 0;
            }else{
                $v['relay'] = 1;
            }
        }
        return $this->fetch('',[
            'title' => '异常警报监控',
            'list' => $list,
        ]);
    }

    /**
     * 电压电流温度监控模块
     */
    public function cvtwarn(){
        $sn = input('get.devicesn');
        if(empty($sn)){
            $this->error('缺少设备编号');
        }
        $list = db('Warning')->where('c_devicesn',$sn)->order('c_time','asc')->paginate();
       return $this->fetch('',[
           'title' => '异常数据监控',
            'list' => $list,
               'sn' => $sn,
           ]
       );
    }

    /**
     * 继电器开合统计
     */
    public function relaywarn()
    {
        $sn = input('get.devicesn');
        if(empty($sn)){
            $this->error('缺少设备编号');
        }
        $list = db('Relay')->where('c_devicesn',$sn)->paginate();
        return $this->fetch('',[
                'title' => '继电器数据监控',
                'list' => $list,
                'sn' => $sn,
            ]
        );
    }

}
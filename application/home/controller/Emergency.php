<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/11 0011
 * Time: 11:40
 */

namespace app\home\controller;


use app\common\lib\Wx;

class Emergency extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'saveemergencyserver'],
    ];
    public function saveEmergencyServer()
    {
        $data = input('post.');
        if(empty($data['phone']))
        {
            return show(0,'缺少定位，或者电话号码');
        }
        $server['c_phone'] = $data['phone'];
        $server['c_lat'] = $data['lat'];
        $server['c_lng'] = $data['lng'];
        $server['c_sex'] = isset($data['sex'])?$data['sex']:'1';
        $server['c_address'] = isset($data['address'])?$data['address']:'';
        $server['c_content'] = isset($data['content'])?$data['content']:'';
        $server['c_username'] = $data['name'];
        $server['c_status'] = 1;
        $res = $this->emergency->add($server);
        if($res)
        {
            $msg['first'] = '您有一个紧急充电请求需要马上处理，请登录后台进行确认';
            $msg['keyword1'] = $this->emergency->id;
            $msg['keyword2'] = '紧急充电用户';
            $msg['keyword3'] = $server['c_address'] ?$server['c_address']:'未获取到地址信息请登入后台查看';
            $msg['keyword4'] = $server['c_username'];
            $msg['keyword5'] = $server['c_phone'];
            $msg['remark'] = '请尽快进入后台确认信息，和进行服务';
            Wx::getInstance()->sendNotice(config('template.adminOpenid'), config('template.emergencyId'),$msg);
            return show(1,'sucess');
        }
        return show(0,'failed');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/29
 * Time: 16:07
 */

namespace app\home\controller;


use think\Request;
use think\Cache;
use app\service\Token;
class User extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' => ['only' => 'gethirebyuserid,getuserinfo,getwxconfig,getrentbyuserid'],
    ];
    //用户租用的信息需要隐藏的字段
    public $hiddenHireInfo = ['c_user_id','c_charge_id','c_last_time','devices.c_deviceid','devices.c_qr_code','devices.c_lng','devices.c_lat','devices.c_operator','devices.c_service_phone','devices.c_status','devices.c_type','devices.c_add_time','devices.c_isdel','devices.c_lease_status'];
    public function getRentByUserId($page=1,$size=15)
    {
        $uid = Token::getCurrentUid();
         $list =  $this->rent->getRentByUserId($uid, $page ,$size);
        if(empty($list))
        {
            return show(0,'found nothing');
        }
        $list->hidden(['c_userid','c_username','c_phone','c_addtime','devices.c_deviceid','devices.c_qr_code','devices.c_lng','devices.c_lat','devices.c_address','devices.c_status','devices.c_type','devices.c_add_time','devices.c_isdel','devices.c_lease_status']);
        foreach ($list as $k => &$v)
        {
            $status = \app\common\lib\Device::getInstance()->getDeviceDetailBySn($v['devices']['c_devicesn']);
            if(empty($status))
            {
                $v['relay'] = '2';
            }else{
                $temp = unserialize($status['c_relay']);
                if($temp['1'] == '1')
                {
                    $v['relay'] = '1';
                    $time = $v['c_chargetime'] + (time()-$v['c_starttime']);
                    $v['chargetime'] = $time;
                }else{
                    $v['relay'] = '0';
                    $time = $v['c_chargetime'];
                    $v['chargetime'] = $time;
                }

            }
            unset($v['c_starttime']);
            unset($v['c_chargetime']);
            unset($v['c_stoptime']);

        }

//        print_r(\app\common\lib\Device::getInstance()->getDeviceDetailBySn('127.0.0.1'));exit;
        return show(1,'sucess',$list);
    }
    /**
     * 用户获取已支付的订单
     * @param $id
     */
    public function getHireByUserId($page=1,$size=15)
    {
        $uid = Token::getCurrentUid();
        $list =  $this->orderStatus->getOrderStatusByUserId($uid, $page ,$size);
        $hire = $list->hidden($this->hiddenHireInfo);
        foreach ($hire as $k => &$v)
        {
            $lasttime = ($v['c_start_time']+3600*$v['c_charge_time'])-time();
            $lasttime = $lasttime <= 0 ? 0 :$lasttime;
            $v['lasttime'] = $lasttime;
        }
        return show(1,'sucess',$list);
    }
    //获取用户细腻
    public function getUserInfo($id)
    {
        if(empty($id))
        {
            return show(0,'缺少用户id');
        }
        $res = $this->user->getUserById($id);
        if(empty($res))
        {
            return show(0,'用户不存在');
        }
        $res->hidden(['c_add_time','c_update_time','c_level','c_integral','c_status']);
        return show(1,'sucess',$res);
    }
    public function getWxConfig()
    {
        $h_url = input("get.url","");
        if(empty($h_url)){
            return show(0,'参数错误');
        }
        $ticket=cache("ticket");
        if(!$ticket) {
            $access_token = cache("access_token");
            if(!$access_token) {
                $this->wxConfig();
                $access_token = cache("access_token");
            }
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
            $result = doCurl($url);
            $result = json_decode($result);
            cache("ticket", $result->ticket, $result->expires_in);
            $ticket=$result->ticket;
        }
        $str_rand=mt_rand(100,999);
        $time=time();
        $string="jsapi_ticket=".$ticket."&noncestr=".$str_rand."&timestamp=".$time."&url=$h_url";
        $signature=sha1($string);
        $data["appId"]=config('service.APPID');
        $data["timestamp"]="$time";
        $data["nonceStr"]="$str_rand";
        $data["signature"]=$signature;
        return json(['status' => 1 ,'data' => $data, 'messagn' => $string]);
    }
}
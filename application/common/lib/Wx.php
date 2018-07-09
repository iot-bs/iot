<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/11 0011
 * Time: 16:41
 */

namespace app\common\lib;

use app\lib\exception\WeChatException;

class Wx
{
    private static $_instance = null;
    public static function getInstance()
    {
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    //微信我二维码配置
    public static function wxConfig(){
        $access_token=cache("access_token");
        if(!$access_token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . config('service.APPID'). "&secret=" .  config('service.APPSECRET');
            $result=doCurl($url);
            $result_arr = json_decode($result);
            try{
                cache("access_token", $result_arr->access_token, $result_arr->expires_in);
            }catch (Exception $e)
            {
                throw new WeChatException();
            }

        }
    }
    /**
     * 发送模板消息
     */
    public static function sendNotice($openId,$templateid,$data,$url = 'http://www.lkgwxm.cn/Emergency/index'){
        //获取access_token
        if (cache('access_token'))
        {
            $access_token2 = cache('access_token');
        }else
        {
            self::wxConfig();
            $access_token2 = cache("access_token");
        }
        $template = 'A7Y9n6naXo4ngG2cYD6VMmwnndD6E53UGaoyoeFbQ18';
        //模板消息
        $json_template = self::json_tempalte($openId,$templateid, $url, $data);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token2;
        $res = self::curl_post($url,urldecode($json_template));
        $result = json_decode($res,true);
        if ($result['errcode']==0){
            return '发送成功';
        }else{
            return '发送失败';
        }
    }

    /**
     * 将模板消息json格式化
     */
    public static function json_tempalte($openid ,$tempalateId , $url ,$data){
        foreach ($data as $k => &$v)
        {
            $v = ['value' =>urlencode($v),'color' => '#FF0000'];
        }
        //模板消息
        $template=array(
            'touser'=>"{$openid}",  //用户openid
            'template_id'=>"{$tempalateId}", //在公众号下配置的模板id
            'url'=>"{$url}", //点击模板消息会跳转的链接
            'topcolor'=>"#7B68EE",
            'data'=> $data
        );
        $json_template=json_encode($template);
        return $json_template;
    }


    /**
     * @param $url
     * @param array $data
     * @return mixed
     * curl请求
     */
    public static function curl_post($url , $data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/13 0013
 * Time: 15:16
 */

namespace Device;

class Wx
{
    static $redisKey = 'iot_access_token';
    static $APPID = 'wxcf7ce9c5ff094a21';
    static $APPSECRET = '1267431c58a4c36fe1af65c29f6f801d';
    static $OPENID = 'ozRvRv4V0nizv-7iCXe9vXf7QvmU';
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
        $access_token = Predis::getInstance()->get(self::$redisKey);
        if(!$access_token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . self::$APPID. "&secret=" . self::$APPSECRET;
            $result = self::doCurl($url);
            $result_arr = json_decode($result);
            try{
                Predis::getInstance()->set(self::$redisKey, $result_arr->access_token, $result_arr->expires_in);
            }catch (\Exception $e)
            {

            }

        }
    }
    public static function doCurl($url,$type=0,$data=[]){
        //初始化
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if($type==1){
            //post
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        }
        //执行并获取内容
        $output=curl_exec($ch);
        //释放curl句柄

        curl_close($ch);

        return $output;


    }
    /**
     * 发送模板消息
     */
    public static function sendNotice($templateid,$data,$url = 'http://www.lkgwxm.cn/Emergency/index')
    {
        $openId = self::$OPENID;
        //获取access_token
        if (Predis::getInstance()->get(self::$redisKey))
        {
            $access_token2 = Predis::getInstance()->get(self::$redisKey);
        }else
        {
            self::wxConfig();
            $access_token2 = Predis::getInstance()->get(self::$redisKey);
        }
        $json_template = self::json_tempalte($openId,$templateid, $url, $data);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token2;
        $res = self::curl_post($url,urldecode($json_template));
        return $res;
//        $result = json_decode($res,true);
//        if ($result['errcode']==0){
//            return '发送成功';
//        }else{
//            return '发送失败';
//        }
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
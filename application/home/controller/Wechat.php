<?php
/**
 * Class WechatController
 * @package Home\Controller
 * 首页进入微信授权
 */

namespace app\home\controller;
use think\Controller;
use app\service\UserToken;
use app\service\Token;
class Wechat extends Base
{
    private $appID;
    private $appsecret ;
    private $redirect_uri;
    public function initialize()
    {
        $this->appID = config('service.APPID');
        $this->appsecret = config('service.APPSECRET');
        $this->redirect_uri = config('service.redirect_uri');
    }
    //获取令牌接口
    public function getToken()
    {
        header("Content-Type: text/html; charset=utf-8");
        //接收跳转地址
        $re_url = input('get.reurl');
        if (empty($re_url)) {
            $re_url = 'index';
        }
        session('re_url',$re_url);
        $Appid = $this->appID;
        //回调地址
//        $urlencode = urlencode($this->redirect_uri);
        $urlencode = $this->redirect_uri;
//        print_r($urlencode);exit;
        //1.获取code
        $code = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $Appid . '&redirect_uri='.$urlencode.'&response_type=code&scope=snsapi_userinfo&state=ucenter#wechat_redirect';

        header("location:" . $code);
    }
    //根据code生成token令牌
    public function buildToken()
    {
        if(request()->isGet())
        {
            $code = input('get.code');
            if (empty($code))
            {
                return show(0, '缺少code');
            }
            $tk = new UserToken($code);
            $token = $tk->get();
            //获取回调的xml数据
            $data = file_get_contents("php://input");
            file_put_contents('/tmp/2.txt',$token,FILE_APPEND);
            header("location:" . "http://www.lkgwxm.cn/websrc/index.html?token=".$token['token']."&uid=".$token['uid']);
        }
        return show(0,'请求错误');

    }
    //验证令牌权限
    public function verifyToken($token=''){
        if(!$token){
            return show(0,'token不允许为空');
        }
        $valid = Token::verifyToken($token);
        if($valid)
        {
            return show(1,'sucess');
        }
        return show(0,'false');
    }

}
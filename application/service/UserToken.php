<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/3
 * Time: 9:31
 */

namespace app\service;
use app\lib\enum\ScopeEnum;
class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;
    protected $user;
    protected $userInfoUrl;

    /**
     * [__construct 构造函数，初始化变量]
     * @param  [type] $code [description]
     * @author liangguangchuan 2017-12-24
     */
    function __construct($code){
        $this->code=$code;
        $this->wxAppID=config('wx.app_id');
        $this->wxAppSecret=config('wx.app_secret');
        $this->wxLoginUrl=sprintf(config('wx.login_url'),$this->wxAppID,$this->wxAppSecret,$this->code);
        $this->userInfoUrl = config('wx.user_info_url');
        $this->user = model('app\admin\model\User');

    }
    public function get(){
        $result=curl_get($this->wxLoginUrl);
        $wxResult=json_decode($result,true);
        if(empty($wxResult)){
            return false;
        }else{
            $loginFail=array_key_exists('errcode',$wxResult);
            $loginFail=array_key_exists('errcode',$wxResult);
            if($loginFail){
                return false;//微信内部异常
            }else{
                return $this->grantToken($wxResult);

            }
        }

    }
    //有令牌就查询令牌 没有就生成令牌
    private function grantToken($wxResult){
        //拿到oppenid
        $openid = $wxResult['openid' ];
        $accessToken = $wxResult['access_token'];
        //数据库里对比一下是否存在openid
        $user=$this->user->getByOpenID($openid);
        if($user){
            $uid=$user->c_id;
        }else{
            $uid=$this->newUser($openid,$accessToken);

        }
        //如果存在，不处理，不存在，就插入记录
        //生成令牌，存入缓存数据，吸入缓存
        $cachedValue = $this->prepareCachedValue($wxResult,$uid);
        $token = $this->saveToCache($cachedValue);
        return ['token' => $token, 'uid' => $uid];

        //吧令牌返回客户端
    }
    private function saveToCache($cachedValue){
        $key=self::generateToken();
        $value=json_encode($cachedValue);
        $expire_in=config('wx.token_expire_in');
        $request = cache($key,$value,$expire_in);
        if(!$request){
            return false;
        }
        return $key;
    }
    private function prepareCachedValue($wxResult,$uid){
        $cachedValue=$wxResult;
        $cachedValue['uid']=$uid;
        //scope 表示app用户权限 32为cms用户权限
        $cachedValue['scope']=ScopeEnum::User;
        return $cachedValue;
    }

    private function newUser($openid,$accessToken){
        $url = sprintf($this->userInfoUrl,$accessToken,$openid);
        $result = curl_get($url);
        $data=json_decode($result,true);
        $user = $this->user->create([
                'c_openid'=>$openid,
                'c_username' => $data['nickname'],
                'c_nickname' => $data['nickname'],
                'c_sex' => $data['sex'],
                'c_image' => $data['headimgurl'],
                'c_add_time' => time()
            ]
        );
        return $user->c_id;
    }

}
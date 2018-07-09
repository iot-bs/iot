<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false) {
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL) {
		return $ip[$type];
	}

	if ($adv) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos = array_search('unknown', $arr);
			if (false !== $pos) {
				unset($arr[$pos]);
			}

			$ip = trim($arr[0]);
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u", ip2long($ip));
	$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
	return $ip[$type];
}

function cvToString($data) {
	$res = '';
	foreach ($data as $k => $v) {
		# code...
		switch ($v['No']) {
		case '1':
			$res .= '接口一:' . ($v['Type'] == 'DC' ? '直流' : '交流') . ':' . $v['Value'] . "<br>";
			break;
		case '2':
			$res .= '接口二:' . ($v['Type'] == 'DC' ? '直流' : '交流') . ':' . $v['Value'] . "<br>";
			break;
		default:
			$res .= 'error';
			break;
		}

	}
	return $res;
}
function doCurl($url,$type=0,$data=[]){
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
//封装的 接口返回数据函数
function show($status,$message='',$data=[]){
    return json([
        'status'=>intval($status),
        'message'=>$message,
        'data'=>$data,
    ]);
}
//支付返回结果集
function payRes($obj,$status,$code){
    $obj->setData('return_code',$status);
    $obj->setData('return_msg',$code);
    return $obj->toXml();
}
//生产32位随机字符串
function getRandChars($length){
    $str=null;
    $strPol="ABCDEFGHIJKOPQRSTUVWXYZ0123456789abcdefghijkopqrstuvwxyz";
    $max=strlen($strPol)-1;
    for ($i=0; $i < $length; $i++) {
        # code...
        $str.=$strPol[rand(0,$max)];
    }
    return $str;
}
//curl请求
function curl_get($url,$httpCode=0){
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    //不做证书校验，部署在linux下需改为true
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
    $file_contents=curl_exec($ch);
    $httpCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $file_contents;
}

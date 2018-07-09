<?php
namespace app\lib\exception;
use think\Exception;
class BaseException extends Exception{
	/**
	 * [$code 状态码 400,200]
	 * @var [type]
	 */
	public $msg='args error';
	public $status;

	public function __construct($params=[]){
		if(!is_array($params)){
			// throw new Exception('参数必须是数组');
			return;
		}
		if(array_key_exists('status', $params)){
			$this->status=$params['status'];
		}
		if(array_key_exists('msg', $params)){
			$this->msg=$params['msg'];
		}

	}

}
/**
* 
*/

<?php
namespace app\lib\exception;
use think\Exception;
use think\exception\Handle;
use think\Request;
use think\Log;
class ExceptionHandler extends Handle{
	private $code;
	private $msg;
	private $errorCode;
	private $status;
	//返回客户端当前请求的url路径
	private $url;

	public function render(\Exception $e){
		if($e instanceof BaseException){
			$this->msg=$e->msg;
			$this->status = $e->status;
		}else{
			if(config('app_debug')){
				return parent::render($e);

			}
			$this->msg='server is wrong,please contace the admin user';
//			$this->recordErrorLog($e);
		}
		$request=new Request();
		$result=[
		    'status' => $this->status,
			'msg'=>$this->msg,
			'request_url'=>$request->url()
		];
		return json($result);
	}
	/**
	 * [recordErrorLog 用于全局定义记录日志功能]
	 * @param  \Exception $e [get a exception class] 
	 * @return [type]        [description]
	 * @author xiaodo 2017-12-18
	 */
	private function recordErrorLog(\Exception $e){
		Log::record($e->getMessage(),'error');
	}
}
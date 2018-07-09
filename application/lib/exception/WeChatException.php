<?php
namespace app\lib\exception;

class WeChatException extends BaseException{
    public $status = 0;
	public $msg='微信服务器接口调用失败';
}
<?php
namespace app\lib\exception;
class TokenException extends BaseException{
	public $status = 4;
	public $msg='Token 已过期，或者无效token';
}
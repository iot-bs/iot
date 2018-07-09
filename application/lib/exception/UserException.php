<?php
namespace app\lib\exception;
class UserException extends BaseException{
    public $status = 0;
	public $msg='用户不存在';
}

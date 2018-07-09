<?php
namespace app\lib\exception;
class ForbiddenException extends BaseException{
    public $status = 0;
	public $msg='权限不够';

}
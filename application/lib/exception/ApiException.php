<?php
namespace app\lib\exception;

class ApiException extends BaseException
{
    public $code = 500;  //http状态码
    public $msg = 'sign异常';
    public $errorCode = 9999;  //业务状态码
}








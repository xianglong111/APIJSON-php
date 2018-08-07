<?php

namespace app\lib\exception;

use think\exception\Handle;
use think\Log;
use think\Request;
use Exception;
use think\Db;
/*
 * 重写Handle的render方法，实现自定义异常消息
 */
class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e)
     {
        
        $retCode = 1; // 系统错误
        if(method_exists($e,'getStatusCode')){
            $retCode = $e->getStatusCode();
        }
        $this->code = 500;
        $this->msg = $e->getMessage();
        $this->errorCode = $retCode;  //业务错误状态码
        
        $result = [
            'msg'  => $this->msg,
            'retcode' => $this->errorCode
        ];
        if(config('app_debug')){
            // 调试状态下需要显示TP默认的异常页面，因为TP的默认页面
            // 很容易看出问题
            $error['file_path']  = $e->getFile();
            $error['error_line'] = $e->getLine();
            $error['error_msg']  = $e->getMessage(); 
            $result['msg']       = $error;
            return json($result,$this->code);
        }
        
        return json($result, $this->code);
    }
}
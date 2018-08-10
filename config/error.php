<?php
/**
 * 错误信息编码
 * code 错误码 0为成功 大于0为错误
 * msg 中文错误提示信息 
 * msg_en 英文错误提示信息
 */
return [

    'DATA_FORMAT_WRONG' => [
        'code'=>1,
        'msg' =>'数据格式错误',
        'msg_en'=>'Data format wrong'
    ],

    'MISSING_PARAMET' => [
        'code'=>2,
        'msg' =>'缺少必要的参数',
        'msg_en'=>'Missing the necessary parameters'
    ],

    'NO_ACCESS_ALLOWED' => [
        'code'=>3,
        'msg' =>'没有相关权限',
        'msg_en'=>'No access is allowed'
    ],

    'LOGIN_TIMEOUT' => [
        'code'=>4,
        'msg' =>'没有相关权限或超时，请您重新登录！',
        'msg_en'=>'No access is allowed or login timeout'
    ],

    'NO_PERMISSIONS_FIELD' => [
        'code'=>5,
        'msg' =>'没有该字段权限',
        'msg_en'=>'No permissions for this field'
    ],

    /**
     * 用户错误提示信息
     * 300开始
      */
    'DUPLICATE_LOGIN' => [
        'code'=>300,
        'msg' =>'重复登录',
        'msg_en'=>'Duplicate login'
    ],

];
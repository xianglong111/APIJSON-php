<?php
// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://jianong.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

// 应用公共文件

function show($status , $message , $data = [] , $httpCode=200) {
    $data = [
        'retcode'=>$status,
        'msg'=>$message,
        'data'=>$data,
    ];
    $httpCode = 200;
    return json($data , $httpCode);
}

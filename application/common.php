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
        'error_code'=>$status,
        'msg'=>$message,
        'data'=>$data,
    ];
    return json($data , $httpCode);
}

/**
 * 获取UID
 */
function getUid(){
    $token = request()->header('Access-Token');
    // 不存在token
    if(is_null($token)){
        return false;
    }
    $uid = cache($token);
    if(is_null($uid) || empty($uid)) {
        return false;
    }else{
        // 重新设置过期时间
        cache($token,$uid,config('token.pc_expiry_time'));
    }
    return $uid;
}

/**
 * 抛出错误信息
 */
function error($sign){
    $msg = config('app_debug')?'msg':'msg_en';
    $sign = 'error.'.$sign;
    abort(config($sign.'.code'),config($sign.'.'.$msg));
}



/**
 * 生成token
 * id+密钥+时间戳 有效时间10分钟
 * @param $id int
 * @return $token string 
 */
 function createToken($id){
    $token = md5($id.config('token.secret_key').time());
    cache($token,$id,config('token.pc_expiry_time'));
    return $token;
}
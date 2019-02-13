<?php

// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018  All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

namespace app\index\controller;
use app\lib\json\JsonParser;

class Home
{  
    public function _empty($name){
        dump($name);
        $json_arr = json_decode(request()->getInput(),true);
        if(is_null($json_arr)){
            error('DATA_FORMAT_WRONG');
        }else{
            $jsonParser = new JsonParser();
            return show(0,'success',$jsonParser->run($json_arr));
        }
    }
}


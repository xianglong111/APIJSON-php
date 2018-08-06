<?php

// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://jianong.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

namespace app\index\controller;
use app\lib\json\JsonParser;

class Home
{
    // 结果对象
    private $data;
    public function __construct(){
        $this->init();
    }

    private function init(){
        $json_arr = json_decode(request()->getInput(),true);
        if(is_null($json_arr)){      
           exception('数据格式错误');
        }else{
           $this->data = JsonParser::run($json_arr,request()->action());
        }
    }
    
    public function get()
    {
        return show(200,'success',$this->data);
    }

    public function gets(){
        return show(200,'success',$this->data);
    }

    public function post(){
        return show(200,'success',$this->data);
    }

    public function posts(){
        return show(200,'success',$this->data);
    }

    public function delete(){
        return show(200,'success',$this->data);
    }
}


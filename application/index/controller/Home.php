<?php
namespace app\index\controller;
use app\lib\json\JsonParser;

class Index
{
    // Json数据对象
    private $json_arr;
    /* 
    * 检查json格式是否正确
    */
    public function __construct(){
        $json_arr = json_decode(file_get_contents("php://input"),true);
        if(is_null($json_arr)){            
           exception('数据格式错误');
        }else{
           $this->json_arr = $json_arr;
        }
     }
    
    public function get()
    {
        return show(200,'success',JsonParser::run($this->json_arr,'get'));
    }    

    public function head(){
        return show(200,'success',JsonParser::run($this->json_arr,'head'));
    }

    public function gets(){
        return show(200,'success',JsonParser::run($this->json_arr,'gets'));
    }

    public function post(){
        return show(200,'success',JsonParser::run($this->json_arr,'post'));
    }

    public function posts(){
        return show(200,'success',JsonParser::run($this->json_arr,'posts'));
    }


    public function delete(){
        return show(200,'success',JsonParser::run($this->json_arr,'delete'));
    }



}


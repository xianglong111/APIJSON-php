<?php
namespace app\index\controller;

class Index{

    public function index(){
        return "Welcome JSON-API`s World!";
    }


    public function test(){
        $key = 'danjiguanjia1234';
        $Aes = new \Aes($key.'1548673415',$key);

        $rs = $Aes->decode('+CEj4j6nOzYbtHudGL2Cu9Bxu9X5hJqX6daaMZIUh7s=');
        halt($rs);
    }
}
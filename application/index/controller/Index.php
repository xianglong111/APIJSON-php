<?php
namespace app\index\controller;

class Index{

    public function index(){
        $a = [2,3,1];
        $b = [1,2,3,4,5];
        $c = array_diff($a, $b);
        dump($c);
        $flag = empty($c)?1 : 0;
        if ($flag) {
        echo "Yes";
        }else {
        echo "No";
        }
    }

}
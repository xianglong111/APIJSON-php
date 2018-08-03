<?php
namespace app\common\model;
use app\common\model\Base as BaseModel;

class User extends BaseModel{

     /**
     * 表名
     * @var string
     */
    protected $table = 'danji_member';
    /**
     * 主键
     * @var int
     */
    protected $pk = 'id';
    /**
     * 允许访问字段
     * @var array
     */
    public $allowed_field = [
        'id',
        'username'
    ];


    /**
     * 用户登陆
     * @access public
     * @param 
     * @return 
     */
    public function login($data){

        
        return $data;

    } 

    /**
     * 用户注册
     * @access public
     * @param 
     * @return 
     */
    public function register(){



    }
}
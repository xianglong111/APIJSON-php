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
     * 自动完成时间字段，默认create_time、update_time，指定名称为create_at、update_at
     * @var string
     */
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';

    /**
     * 用户登陆
     * @access public
     * @param 
     * @return 
     */
    public function login($data){
        $uid = getUid();
        if($uid !== false) abort(302, '已登录');
        $uid = 1;
        
        $user['token'] = $this->token($uid);
        return $user;
    } 

    /**
     * 用户注册
     * @access public
     * @param 
     * @return 
     */
    public function register(){



    }

    /**
     * 生成token
     * uid+密钥+时间戳 有效时间15分钟
     * @param $uid int
     * @return $token string 
     */
    private function token($uid){
        $token = md5($uid.config('user.secret_key').time());
        cache($token,$uid,900);
        return $token;
    }
}
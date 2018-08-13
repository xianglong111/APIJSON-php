<?php
namespace app\index\model;
use app\common\model\Base as BaseModel;

class ActiveCollect extends BaseModel{

     /**
     * 表名
     * @var string
     */
    protected $table = 'danji_active_collect';
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
        'uid'
    ];

}
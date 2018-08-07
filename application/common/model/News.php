<?php
namespace app\common\model;
use app\common\model\Base as BaseModel;

class News extends BaseModel{

    /**
     * 表名
     * @var string
     */
    protected $table = 'danji_news';

    /**
     * 主键
     * @var int
     */
    public $pk = 'id';
    /**
     * 允许访问字段
     * @var array
     */
    public $allowed_field = [
        'id',
        'uid',
        'title',
        'description',
        'category',
        'author',
        'cover',
        'create_time'
    ];

    /**
     * 关联模型：NewsDetail
     * @access public
     * @param 
     * @return 
     */
    public function NewsDetail()
    {
        return $this->hasOne('NewsDetail','news_id','id')
                    //->bind('news_id')
                    ->setEagerlyType(0);
    }

    /**
     * 关联模型：User
     * @access public
     * @param 
     * @return 
     */
    public function User(){
        return $this->hasOne('User','id','uid')
                    ->setEagerlyType(0);
    }

    /**
     * 标题字段修改器
     * @access public
     * @param  string $value 标题
     * @return
     */
    public function getTitleAttr($value)
    {
        return $value.' - ouyang';
    }


}
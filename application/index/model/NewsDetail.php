<?php
namespace app\index\model;
use app\common\model\Base as BaseModel;

class NewsDetail extends BaseModel{

    protected $table = 'danji_news_detail';
    /**
     * 主键
     * @var int
     */
    protected $pk = 'news_id';
    // 允许访问字段
    public $allowed_field = [
        'news_id',
        'content'
    ];
    
    public function news()
    {
        return $this->belongsTo('News','id');
    }

}
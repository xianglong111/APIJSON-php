<?php
namespace app\common\model;
use think\Model;

class Base extends Model{

    /**
     * 允许访问字段
     * @var array
     */
    protected $allowed_field = [];

    /**
     * 条件语句，where
     * @var string
     */
    protected $where = '';

    /**
     * 数据分页
     * @var string
     */
    protected $page = '';

    /**
     * 数据条数,默认十条数据
     * @var int
     */
    protected $count = 10;

    /**
     * 数据条数,默认十条数据
     * @var int
     */
    protected $limit = 10;

    /**
     * 排序
     * @var string
     */
    protected $order = '';

    /**
     * 关联模型
     * @var string
     */
    protected $with = [];

    /**
     * 模型数组
     * @var array
     */
    public $model_arr = '';


   
    /**
     * 初始化数据
     * @access public
     * @param  array|object $data 数据
     */
    public function initData($model_arr){

        $this->model_arr = $model_arr;
        // 验证参数
        $this->checkParams();

    }

    /**
     * 检测参数正确性
     * @access public
     * @param  array|object $this->model_arr 模型数组，包含（table、field、page、count等）
     */
    private $relation_model;
    protected function checkParams(){
        // 检测查询字段是否有权限
        if($this->model_arr['@field'] != ''){
            $this->allowed_field = $this->checkFieldAllowed($this->model_arr['@field'], $this->allowed_field); 
        }

        // 条件语句
        $this->where = $this->model_arr['@where'];

        // 分页
        $this->page      = $this->model_arr['@page'];

        // 限制条数
        if($this->model_arr['@limit'] != ''){
            $this->limit = $this->model_arr['@limit'];
        }

        // 获取总数
        $this->count = $this->model_arr['@count'];
        
        // 排序
        $this->order     = $this->model_arr['@order'];

        // 关联模型 输入参数：model1:field1,field2;model2:field1,field2
        //         输出结果：[ model1=>'field1,field2' , model2=>'field1,field2' ]
        if($this->model_arr['@with'] != ''){
            $relation_model_arr = explode(';',$this->model_arr['@with']);
            // 确定数据正确性
            if(is_array($relation_model_arr)){
                foreach($relation_model_arr as $k=>$relation_model){
                    $relation_model  = explode(':',$relation_model);
                    $this->with[$relation_model[0]] = function($query) use ($relation_model){
                        $query->withField($relation_model[1]);
                    };
                }
            }
        }

        if($this->model_arr['with'] != ''){
            if(is_array($this->model_arr['with'])){
                $this->with = [];
                foreach($this->model_arr['with'] as $model_name=>$field){
                    // 检测字段是否有权限
                    $model = model($model_name);
                    $field = $this->checkFieldAllowed($field,$model->allowed_field);
                    $this->with[$model_name] = function($query) use ($field){
                        $query->withField($field);
                    };
                }
            }
        }
    }

    /**
     * 检查字段是否允许操作
     * @access protected
     * @param  array   $field 当前字段列表
     * @param array   $allowed_field 允许字段列表
     * @return
     */
    protected function checkFieldAllowed($field,$allowed_field){
        if($field == '')return $allowed_field;
        if(!is_array($field))$field = explode(',',$field);
        $allowed_field_rs = array_diff($field, $allowed_field);
        // 差集数组为空，则说明通过
        if(empty($allowed_field_rs)){
            return $field;
        }else{
            exception('没有该字段权限');
        }
    }

    /**
     * 查询单条数据
     * @access public
     * @param  array
     * @return
     */
    public function findOne(){
        return $this->field($this->allowed_field)
                        ->with($this->with)
                        ->where($this->where)
                        ->order($this->order)
                        ->find();
    }

    /**
     * 查询多条数据
     * @access public
     * @param  array
     * @return
     */
    public function findAll(){
        return $this->field($this->allowed_field)
                    ->with($this->with)
                    ->where($this->where)
                    ->page($this->page)
                    ->limit($this->limit)
                    ->order($this->order)                        
                    ->select();
    }

    /**
     * 获取总数量
     * @access public
     * @param  array
     * @return
     */
    public function getCount(){
        return $this->with($this->with)
                    ->where($this->where)
                    ->count($this->count);
    }




}
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
     * 初始化数据
     * @access public
     * @param  array|object $data 数据
     */
    public function initData($model_arr){
        // 验证参数
        $this->checkParams($model_arr);

    }

    /**
     * 检测参数正确性
     * @access public
     * @param  array|object $model_arr 模型数组，包含（table、field、page、count等）
     */
    protected function checkParams($model_arr){
        // 检测查询字段是否有权限
        if(array_key_exists('field',$model_arr)){
            $this->allowed_field = $this->checkFieldAllowed($model_arr['field'], $this->allowed_field); 
        }

        // 条件语句
        if(array_key_exists('where',$model_arr))$this->where = $model_arr['where'];

        // 分页
        if(array_key_exists('page',$model_arr))$this->page   = $model_arr['page'];

        // 限制条数
        if(array_key_exists('limit',$model_arr)){
            $this->limit = $model_arr['limit'];
        }

        // 获取总数
        if(array_key_exists('count',$model_arr))$this->count = $model_arr['count'];
        
        // 排序
        if(array_key_exists('order',$model_arr))$this->order  = $model_arr['order'];

        // 关联模型 输入参数:['model'=>'field1,field2,field3']
        if(array_key_exists('with',$model_arr)){
            if(is_array($model_arr['with'])){
                $this->with = [];
                foreach($model_arr['with'] as $model_name=>$field){
                    // 检测字段是否有权限
                    $model = model($model_name);
                    $field = $this->checkFieldAllowed($field,$model->allowed_field);
                    $this->with[$model_name] = function($query) use ($field,$model_name){
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
     * @return array
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
     * @return array
     */
    public function findAll(){
        return $this->field($this->allowed_field)
                    ->with($this->with)
                    ->where($this->where)
                    ->page($this->page)
                    ->limit($this->limit)
                    ->order($this->order)
                    ->fetchSql(true)                      
                    ->select();
    }

    /**
     * 获取总数量
     * @access public
     * @return int
     */
    public function getCount(){
        return $this->with($this->with)
                    ->where($this->where)
                    ->count($this->count);
    }

    /**
     * 执行模型自定义方法
     * @access public
     * @param  string fun_name 方法名
     * @param  array  data 参数
     * @return
     */
    public function exeFun($fun_name,$data){
        return call_user_func_array([$this,$fun_name],[$data]);
    }


}
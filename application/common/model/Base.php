<?php
// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://jianong.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

namespace app\common\model;
use think\Model;

class Base extends Model{

    /**
     * 查询条件
     * @var array
     */
    protected $sql_condition = [
        'field' => '',
        'where' => '',
        'page'  => '',
        'limit' => 10,
        'order' => '',
        'group' => '',
        'with'  => []
    ];

    /**
     * uid字段名
     * @var string
     */
    private $count;

    /**
     * uid字段名
     * @var string
     */
    protected $uid_name = 'uid';
    
    /**
     * uid
     * @var mixed
     */
    protected $uid = false;

    /**
     * uid字段条件语句
     * @var string
     */
    protected $uid_condition = '';

    /**
     * 聚合函数
     * @var const
     */
    private $ploy_function = [
        'count'=>'',
        'sum'=>'',
        'max'=>'',
        'min'=>'',
        'avg'=>''
    ];

    /**
     * 初始化数据
     * @access public
     * @param  array|object $data 数据
     */
    public function initData($model_field){
        // 设置默认字段
        $this->sql_condition['field'] = $this->allowed_field;
        
        $model_arr = [];
        if(is_array($model_field)){
            foreach ($model_field as $model_child_name => $model_child) {
                // 批量新增和修改的操作
                if($model_child_name === 0) return $model_field;
                // 是否为字段，判断标准为是否为数组
                $is_field = !is_array($model_field[$model_child_name]);
                if($is_field){
                    $model_arr[$model_child_name]  = $model_child;
                }else{
                    $model_arr['with'][$model_child_name] = "";
                    foreach($model_child as $key=>$field){
                        $model_arr['with'][$model_child_name] = $field;
                    }
                }
            }
        }
        $this->checkParams($model_arr);
        return $model_arr;
    }

    /**
     * 检测参数正确性
     * @access public
     * @param  array|object $model_arr 模型数组，包含（table、field、page、count等）
     */
    protected function checkParams($model_arr){
        if(empty($model_arr)) return [];

        foreach($model_arr as $key=>$value){
            if(array_key_exists($key,$this->sql_condition)){
                $method_name = 'handle'.ucfirst($key);
                if(method_exists($this,$method_name)){
                    call_user_func_array([$this,$method_name],[$value]);
                }else{
                    $this->sql_condition[$key] = $value;
                }
            }elseif(array_key_exists($key,$this->ploy_function)){
                if(!empty($value)){
                    $this->ploy_function[$key] = $value;
                }
            }
        }
    }

    /**
     * 处理关联模型
     * @access protected
     * @param  array   $with 关联模型的值
     * @return
     */
    private function handleWith($with){
        if(empty($with)) return [];
        foreach($with as $model_name=>$field){
            $model = model($model_name);
            $field = $this->checkFieldAllowed($field,$model->allowed_field);
            $this->sql_condition['with'][$model_name] = function($query) use ($field,$model_name){
                $query->withField($field);
            };
        }
    }

    /**
     * 处理字段方法
     * @access protected
     * @param  array   $field 当前字段列表
     * @return
     */
    protected function handleField($field){
        $this->sql_condition['field'] = $this->checkFieldAllowed($field,$this->allowed_field);
    }

    /**
     * 检查字段是否允许操作
     * @access protected
     * @param  array   $field 当前字段列表
     * @param array   $allowed_field 允许字段列表
     * @return
     */
    private function checkFieldAllowed($field,$allowed_field){
        if($field == ''){
            $field = $allowed_field;
        }
        if(!is_array($field)){
            $field = explode(',',$field);
        }           
        if(!empty(array_diff($field, $allowed_field))){
            error('NO_PERMISSIONS_FIELD');
        }       
        return $field;
    }

    /**
     * 检查字段是否允许操作
     * @access protected
     * @param  array   $field 当前字段列表
     * @param array   $allowed_field 允许字段列表
     * @return
     */
    public function setUidCondition(){
        $this->uid = getUid();
        if($this->uid == false) error('LOGIN_TIMEOUT');
        if(!empty($this->with)){
            $alias = str_replace(config('database.prefix'),'',$this->table);
            $this->uid_condition = $alias.'.'.$this->uid_name.'='.$this->uid;
        }
    }

    /**
     * 判断用户是否为当前操作用户
     * @access public
     * @param  int $pk 主键值
     * @return bool
     */
    protected function checkUser($pk){
        $user = $this->where($this->uid_name,$this->uid)->where($this->pk,$pk)->value($this->uid_name);
        if(!$user) error('NO_ACCESS_ALLOWED');
    }

    /**
     * 模型条件SQL
     * @access public
     * @return array
     */
    protected function getModel(){
        $model = $this->field($this->sql_condition['field']);
        foreach($this->sql_condition as $field=>$condition){
            if($field != 'field' && $condition != ''){
                $model = call_user_func_array([$model,$field],[$this->sql_condition[$field]]);
            }
        }
        return $model;
    }

    /**
     * 查询单条数据
     * @access public
     * @return array
     */
    public function findOne(){
        return $this->getModel()->find();
    }

    /**
     * 查询多条数据
     * @access public
     * @return array
     */
    public function findAll(){
        return $this->getModel()->select();
    }

    /**
     * 验证token新增修改一条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateOne($data)
    {
        $is_update = array_key_exists($this->pk,$data);
        if($this->uid !== false){
            if($is_update){
                $this->checkUser($data[$this->pk]);
            }else{
                $data[$this->uid_name] = $this->uid;
            }
        }
        return $this->allowField($this->allowed_field)->isUpdate($is_update)->save($data) !== false;
    }

    /**
     * 验证token新增修改多条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateAll($datas)
    {
        if($this->uid !== false){
            foreach($datas as $key=>$data){
                if(array_key_exists($this->pk,$data)){
                    $this->checkUser($data[$this->pk]);
                }else{
                    $data['uid'] = $this->uid;
                    $datas[$key] = $data;
                }
            }
        }
        return $this->allowField($this->allowed_field)->saveAll($datas) !== false;
    }

    /**
     * 删除记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function deleteAll($data)
    {
        $resultSet = $this->select($data);
        if (count($resultSet) === 0){
            return false;
        }else{
            foreach ($resultSet as $data) {
                $rs = $data->delete();
                if($rs === false) return false;
            }
        }
        return true;
    }

    /**
     * 获取聚合函数数据
     * @access public
     * @return int
     */
    public function getPloy($ploy){
        $model = $this->getModel();
        return call_user_func_array([$model,$ploy],[$this->ploy_function[$ploy]]);
    }

    /**
     * 执行自定义模型方法
     * @access public
     * @param  string
     * @param  array
     * @return
     */
    public function exeFun($fun_name,$data){
        return call_user_func_array([$this,$fun_name],[$data]);
    }
}
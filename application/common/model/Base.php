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
     * uid字段名
     * @var string
     */
    protected $uid_name = 'uid';
    
    /**
     * uid字段名
     * @var mixed
     */
    protected $uid = false;

    /**
     * uid字段条件语句
     * @var string
     */
    protected $uid_condition = '';

    /**
     * 初始化数据
     * @access public
     * @param  array|object $data 数据
     */
    public function initData($model_field){
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
        // 验证参数
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

        // 关联模型
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
            error('NO_PERMISSIONS_FIELD');
        }
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
     * 查询单条数据
     * @access public
     * @return array
     */
    public function findOne(){
        return $this->field($this->allowed_field)
                        ->with($this->with)
                        ->where($this->where)
                        ->where($this->uid_condition)
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
                    ->where($this->uid_condition)
                    ->page($this->page)
                    ->limit($this->limit)
                    ->order($this->order)
                    ->select();
    }

    /**
     * 判断用户是否为当前操作用户
     * @access public
     * @param  mixed $data 
     * @return bool
     */
    protected function checkUser($pk){
        // 判断操作用户是否当前用户
        $user = $this->where($this->uid_name,$this->uid)->where($this->pk,$pk)->column($this->uid_name);
        if(!$user) error('NO_ACCESS_ALLOWED');
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
            // 判断为新增还是修改
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
        if(!is_array($datas)) return false;
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
     * 获取总数量
     * @access public
     * @return int
     */
    public function getCount(){
        return $this->with($this->with)
                    ->where($this->where)
                    ->where($this->uid_condition)
                    ->count($this->count);
    }

    /**
     * 执行模型方法
     * @access public
     * @param  string
     * @param  array
     * @return
     */
    public function exeFun($fun_name,$data){
        return call_user_func_array([$this,$fun_name],[$data]);
    }
}
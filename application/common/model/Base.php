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
     * 更新主键值
     * @var array
     */
    protected $updatePk = [];

    /**
     * uid字段名
     * @var string
     */
    public $uid_name = 'uid';

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

        if(empty($model_arr)) return;

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
            exception('没有该字段权限');
        }
    }

    /**
     * 检测token
     * @access public
     * @return array
     */
    public function getUid(){
        $uid = getUid();
        if($uid == false) exception('没有相关权限或超时，请您重新登录！');
        return $uid;
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
                    ->select();
    }

    /**
     * 验证token查询单条数据
     * @access public
     * @return array
     */
    public function findOnes($uid){
        return $this->field($this->allowed_field)
                        ->with($this->with)
                        ->where($this->where)
                        ->where($this->table.' '.$$this->uid_name,$uid)
                        ->order($this->order)
                        ->find();
    }

    /**
     * 验证token查询多条数据
     * @access public
     * @return array
     */
    public function findAlls($uid){
        return $this->field($this->allowed_field)
                    ->with($this->with)
                    ->where($this->where)
                    ->where($this->table.'.'.$this->uid_name,$uid)
                    ->page($this->page)
                    ->limit($this->limit)
                    ->order($this->order) 
                    //->fetchSql(true)
                    ->select();
    }

    /**
     * 新增修改一条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateOne($data)
    {
        // 判断为新增还是修改
        if(array_key_exists($this->pk,$data)){
            return $this->allowField($this->allowed_field)->update($data) !== false;
        }else{
            return $this->allowField($this->allowed_field)->insert($data) !== false;
        }
    }

    /**
     * 新增修改多条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateAll($data)
    {
        return $this->allowField($this->allowed_field)->saveAll($data) !== false;
    }

    /**
     * 验证token新增修改一条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateOnes($data,$uid)
    {
        if(array_key_exists($this->pk,$data)){
            $pk = $data[$this->pk];
            unset($data[$this->pk]);
            $rs = $this->allowField($this->allowed_field)
                        ->where($this->uid_name,$uid)
                        ->where($this->pk,$pk)
                        ->data($data)
                        ->update();
            return $rs!== false;
        }else{
            // 新增时加入UID
            $data[$this->uid_name] = $uid;
            return $this->allowField($this->allowed_field)->insert($data) !== false;
        }
    }

    /**
     * 验证token新增修改多条记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @return bool
     */
    public function updateAlls($datas,$uid)
    {
        // 判断为新增还是修改
        $is_update = array_key_exists($this->pk,$datas[0]);
        foreach($datas as $data){
            if($is_update){
                $pk  = $data[$this->pk];
                unset($data[$this->pk]);
                $rs = $this->allowField($this->allowed_field)
                            ->where($this->uid_name,$uid)
                            ->where($this->pk,$pk)
                            ->data($data)
                            ->update();
            }else{
                $data['uid'] = $uid;
                $rs = $this->insert($data);
            }
        }
        return $rs !== false;
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
                    ->count($this->count);
    }

    /**
     * 获取总数量
     * @access public
     * @return int
     */
    public function getCounts($uid){
        return $this->with($this->with)
                    ->where($this->where)
                    ->where($this->uid_name,$uid)
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
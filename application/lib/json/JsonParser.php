<?php
namespace app\lib\json;
/**
 * 需要完成新的功能有
 * 1、模块的编写标准，大驼峰命名法，例如：User、NewsDetail
 * 2、字段的编写为小写字母例如：limit、field
 * 3、验证token
 * 
 */


class JsonParser{
    /**
     * 数据结果
     * @var array
     */
    private static $data = [];

    /**
     * Json数组对外解析器
     * @access public
     * @param  string $json_arr JSON数据
     * @return array
     */
    public static function run($json_arr,$handle_type = 'get'){
        if(empty($json_arr)) return [];           
        foreach ($json_arr as $model_name => $model_field) {
            // 获取表名，转换成小写
            $table_name = parse_name($model_name);
            // 判断是否为数组
            $condition['is_arr'] = strpos($model_name,'[]')!==false;
            if( $condition['is_arr'] ) {
                $table_name  = str_replace('[]','',$table_name);
            }
            // 判断是否为方法
            $condition['is_fun'] = strpos($model_name,'.')!==false;
            if( $condition['is_fun'] ) {
                $table_name  = explode('.',$table_name);
            }

            call_user_func("self::".$handle_type,$model_name,$model_field,$table_name,$condition);
        }
        return self::$data;
    }

    /**
     * 查询数据
     * @access public
     * @param  string $model_name 模型名称
     * @return array
     */
    private static function get($model_name,$model_field,$table_name,$condition){
        // 关键词，先定义避免出现索引不存在的情况
        $model_arr = [];

        if(is_array($model_field)){                             
            foreach ($model_field as $model_child_name => $model_child) {
                // 小写字母为字段,驼峰为模型
                $is_field = strtolower($model_child_name) === $model_child_name;
                if($condition['is_fun']||$is_field){
                    $model_arr[$model_child_name]  = $model_child;
                }else{
                    foreach($model_child as $key=>$field){
                        if($key == "field")$model_arr['with'][$model_child_name] = $field;
                    }
                }
            }
        }
        
        if($condition['is_fun']){
            $model = model($table_name[0]);
            // 获取方法名称
            self::$data[$table_name[0]] = $model->exeFun($table_name[1],$model_arr);
        }else{
            $model = model($table_name);
            $model->initData($model_arr);
            self::$data[$model_name] = $condition['is_arr']?$model->findAll():$model->findOne();
            // 获取总数
            if($condition['is_arr'] && array_key_exists('count',$model_arr)){
                self::$data[$model_name.".count"] = $model->getCount();
            }
        }
    }

    /**
     * 修改添加数据
     * @access public
     * @param  array json_arr json数组
     * @return array
     */
    public static function post($model_name,$model_field,$table_name,$condition){
        $pk = [];
        foreach($model_field as $field_name=>$field){
            if(strpos($field_name,'@') !== false){
                $pk[str_replace('@','',$field_name)] = $field;
                unset($model_field[$field_name]);
            }
        }
        $rs = [];
        if($condition['is_fun']){
            $model = model($table_name[0]);
            // 获取方法名称
            $rs = $model->exeFun($table_name[1],$model_field);
        }else{
            // 实例化模型
            $model = model($table_name);
            $result = true;
            if($condition['is_arr']){
                $result = $model->allowField($model->allowed_field)->saveAll($model_field);
            }else{
                if(empty($pk)){
                    $result = $model->allowField($model->allowed_field)->fetchSql(true)->insert($model_field);
                }else{
                    $result = $model->allowField($model->allowed_field)->save($model_field,$pk);
                }
            }
            
            if($result === false) exception('数据操作失败');
            $rs['result'] = $result;
        }
        self::$data[$model_name] = $rs;
    }

    /**
     * 删除数据
     * @access public
     * @param  array json_arr json数组
     * @return array
     */
    public static function delete($model_name,$model_field,$table_name,$condition){
        $ids = false;
        foreach($model_field as $field_name=>$field){
            if(strpos($field_name,'@') !== false)$ids = $field;
        }
        if($ids === false)exception('缺少必要的参数');
        $rs = [];
        if($condition['is_fun']){
            $model = model($table_name[0]);
            // 获取方法名称
            $rs = $model->exeFun($table_name[1],$ids);
        }else{
            $rs['result'] = model($model_name)::destroy($ids);
        }
        self::$data[$model_name] = $rs;
    }
}
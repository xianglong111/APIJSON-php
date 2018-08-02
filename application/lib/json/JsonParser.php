<?php
namespace app\lib\json;

class JsonParser{

    /**
     * 默认条件语句
     * @var array
     */
    private static $conditions   = [
        '@where'=>'',
        '@field'=>'',
        '@page'=>'',
        '@count'=>'',
        '@order'=>'',
        '@with'=>'',
        'with'=>[]
    ];

    /**
     * Json数组对外解析器
     * @access public
     * @param  string $json_arr JSON数据
     * @return array
     */
    public static function run($json_arr,$handle_type = 'get'){
        if(empty($json_arr)) return [];
        return call_user_func("self::".$handle_type,$json_arr);
    }

    /**
     * 查询数据
     * @access public
     * @param  string $model_name 模型名称
     * @return array
     */
    private static function get($json_arr){
        $data = [];            
        foreach ($json_arr as $model_name => $model_field) {   
            // 关键词，先定义避免出现索引不存在的情况
            $model_arr = self::$conditions;
            // 设置模型名称
            $model_arr['table']  = str_replace('[]','',$model_name);
            // 判断是否为数组
            $model_arr['is_arr'] = strpos($model_name,'[]')===false;

            if(is_array($model_field)){                             
                foreach ($model_field as $model_child_name => $model_child) {
                    if(strpos($model_child_name,'@') === false){
                        $model_arr['with'][$model_child_name] = '';
                        foreach($model_child as $key=>$field){
                            if($key == "@field")$model_arr['with'][$model_child_name] = $field;
                        }
                    }else{
                        $model_arr[$model_child_name]  = $model_child;
                    }
                }
            }
            // 实例化模型
            $model = app()->model($model_arr['table']);
            $model->initData($model_arr);
            $data[$model_name] = $model_arr['is_arr']?$model->findOne():$model->findAll();
        }
        return $data;
    }


    /**
     * 添加数据
     * @access public
     * @param  array json_arr json数组
     * @return array
     */
    public static function post($json_arr){
        $data = [];
        foreach ($json_arr as $model_name => $model_field) {
            $pk = false;
            foreach($model_field as $field_name=>$field){
                if(strpos($field_name,'@') !== false){
                    $pk[str_replace('@','',$field_name)] = $field;
                    unset($model_field[$field_name]);
                }
            }
            // 实例化模型
            $table_name= str_replace('[]','',$model_name);
            $model = model($table_name);
            $rs = [];
            $result = true;
            if(strpos($model_name,'[]')===false){
                if($pk === false){// 新增
                    $result = $model->allowField($model->allowed_field)->save($model_field);
                }else{// 修改
                    $result = $model->allowField($model->allowed_field)->save($model_field,$pk);
                }
            }else{
                $result = $model->allowField($model->allowed_field)->saveAll($model_field);
            }
            if($result === false)exception('数据操作失败');
            $rs['result'] = $result;
            $data[$model_name] = $rs;
        }
        return $data;
    }

    /**
     * 删除数据
     * @access public
     * @param  array json_arr json数组
     * @return array
     */
    public static function delete($json_arr){
        $data = [];
        foreach ($json_arr as $model_name => $model_field) {
            $ids = false;
            foreach($model_field as $field_name=>$field){
                if(strpos($field_name,'@') !== false){
                    $ids = $field;
                }
            }
            if($ids === false)exception('缺少必要的参数');
            $rs['result'] = model($model_name)::destroy($ids);
            $data[$model_name] = $rs;
        }
        return $data;
    }

    
}
<?php
namespace app\lib\json;
/***
 * 新功能
 * 1、数据验证
 * 2、访问权限功能
 * 
  */
class JsonParser{

    /**
     * Json数组对外解析器
     * @access public
     * @param  string $json_arr JSON数据
     * @return array
     */
    public static function run($json_arr,$handle_type = 'get'){
        if(empty($json_arr)) return [];
        $data = [];
        foreach ($json_arr as $model_name => $model_field) {
            // 获取表名，转换成小写
            $table_name = $model_name;
            // 判断是否为数组
            $is_arr = strpos($model_name,'[]')!==false;
            if( $is_arr ) $table_name  = str_replace('[]','',$table_name);
            // 判断是否为方法
            $is_fun = strpos($model_name,'.')!==false;
            $action_name = '';
            if( $is_fun ) list($table_name,$action_name) = explode('.',$table_name);
            
            // 实例化模型
            $model = model($table_name);
            $model_arr = $model->initData($model_field);
            if(empty($model_arr))exception('缺少必要的参数');

            // 执行自定义方法
            if( $is_fun ) {
                $data[$model_name] = $model->exeFun($action_name,$model_arr);
            }else{
                // 查询
                if($handle_type == 'get'){
                    $data[$model_name] = $is_arr?$model->findAll():$model->findOne();
                    // 获取总数
                    if($is_arr && array_key_exists('count',$model_arr)){
                        $data[$table_name.".count"] = $model->getCount();
                    }
                }elseif($handle_type == 'post'){ // 新增和修改
                    $data[$model_name]['result'] = $model->updateAll($model_arr,$is_arr);
                }elseif($handle_type == 'delete'){ // 删除数据
                    $data[$model_name]['result'] = model($table_name)->deleteAll(reset($model_arr)) !== false;
                }
            }  
        }
        return $data;
    }
}
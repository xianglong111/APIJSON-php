<?php
// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://jianong.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

namespace app\lib\json;
class JsonParser{
    
    /**
     * Json数组对外解析器
     * @access public
     * @param  string $json_arr JSON数据
     * @return array
     */
    public function run($json_arr){
        // 获取当前方法名
        $handle_type = request()->action();
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
            if(!empty($model_field)&&empty($model_arr)) exception('缺少必要的参数');

            // 执行自定义方法
            if( $is_fun ) {
                $data[$model_name] = $model->exeFun($action_name,$model_arr);
            }else{
                if(in_array($table_name,config('model.no_access_allowed'))) exception( '不允许访问' );
                // 查询
                if($handle_type == 'get'){
                    $data[$model_name] = $is_arr?$model->findAll():$model->findOne();
                    // 获取总数
                    if($is_arr && array_key_exists('count',$model_arr)){
                        $data[$table_name.".count"] = $model->getCount();
                    }
                }elseif($handle_type == 'post'){ // 新增和修改
                    $data[$model_name]['result'] = $is_arr?$model->updateAll($model_arr):$model->updateOne($model_arr);
                }elseif($handle_type == 'delete'){ // 删除数据
                    $data[$model_name]['result'] = model($table_name)->deleteAll(reset($model_arr)) !== false;
                }else{
                    $uid = $model->getUid();
                    if($handle_type == 'gets'){
                        $data[$model_name] = $is_arr?$model->findAlls($uid):$model->findOnes($uid);
                        // 获取总数
                        if($is_arr && array_key_exists('count',$model_arr)){
                            $data[$table_name.".count"] = $model->getCounts($uid);
                        }
                    }elseif($handle_type == 'posts'){
                        $data[$model_name]['result'] = $is_arr?$model->updateAlls($model_arr,$uid):$model->updateOnes($model_arr,$uid);
                    }
                }  
            }
        }
        return $data;
    }

}




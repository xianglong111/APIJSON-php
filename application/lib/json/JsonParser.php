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
     * token验证的方法
     * @var array
     */
    private $token_methods = ['gets','posts'];

    /**
     * 数组常量标识
     * @var const
     */
    private const ARRAY_SIGN = '[]';

    /**
     * 方法常量标识
     * @var const
     */
    private const METHOD_SIGN = '.';

    /**
     * 结果常量标识
     * @var const
     */
    private const RESULT_SIGN = 'result';

    /**
     * 总数常量标识
     * @var const
     */
    private const COUNT_SIGN = 'count';

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
            
            $table_name = $model_name;
            
            $is_arr = strpos($model_name,self::ARRAY_SIGN)!==false;
            if( $is_arr ) $table_name  = str_replace(self::ARRAY_SIGN,'',$table_name);
            
            $is_fun = strpos($model_name,self::METHOD_SIGN)!==false;
            $action_name = '';
            if( $is_fun ) list($table_name,$action_name) = explode(self::METHOD_SIGN,$table_name);
            
            // 实例化模型
            $model = model($table_name);
            $model_arr = $model->initData($model_field);
            if(!empty($model_field) && empty($model_arr)) error('MISSING_PARAMET');

            // 执行自定义方法
            if( $is_fun ) {
                $data = $model->exeFun($action_name,$model_arr);
            }else{
                $no_access_allowed = config('model.no_access_allowed');
                if(in_array($table_name,$no_access_allowed)) error('NO_ACCESS_ALLOWED');

                if(in_array($handle_type,$this->token_methods)){
                    $model->setUidCondition();
                    $handle_type = substr($handle_type, 0, -1);
                }
                $result = call_user_func_array([$this,$handle_type],[$model,$model_arr,$is_arr]);
                if($handle_type == 'get'){
                    $data[$model_name] = $result;
                    if($is_arr && array_key_exists(self::COUNT_SIGN,$model_arr)){
                        $data[$table_name.'.'.self::COUNT_SIGN] = $this->count($model);
                    }
                }else{
                    $data[$model_name][self::RESULT_SIGN] = $result;
                }
            }
        }
        return $data;
    }

    /**
     * 获取数据
     * @access public
     * @param  model $model 模型对象
     * @param  array $model_arr 模型数据
     * @param  bool  $is_arr
     * @return array 
     */
    private function get($model,$model_arr,$is_arr){
        return $is_arr?$model->findAll():$model->findOne();
    }
    /**
     * 新增修改数据
     * @access public
     * @param  model $model 模型对象
     * @param  array $model_arr 模型数据
     * @param  bool  $is_arr
     * @return array
     */
    private function post($model,$model_arr,$is_arr){
        return $is_arr?$model->updateAll($model_arr):$model->updateOne($model_arr);
    }
    /**
     * 删除数据
     * @access public
     * @param  model $model 模型对象
     * @param  array $model_arr 模型数据
     * @param  bool  $is_arr 是否为数组
     * @return array
     */
    private function delete($model,$model_arr,$is_arr){
        return $model->deleteAll(reset($model_arr)) !== false;
    }
    /**
     * 获取数据总数
     * @access public
     * @param  model $model 模型对象
     * @return array
     */
    private function count($model){
        return $model->getCount();
    }


}




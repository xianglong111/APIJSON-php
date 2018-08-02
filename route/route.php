<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::post('get', 'index/home/get')->allowCrossDomain();
Route::post('post', 'index/home/post')->allowCrossDomain();
Route::post('delete', 'index/home/delete')->allowCrossDomain();
Route::post('count', 'index/home/count')->allowCrossDomain();
Route::post('gets', 'index/home/gets')->allowCrossDomain();
Route::post('posts', 'index/home/posts')->allowCrossDomain();
return [

];

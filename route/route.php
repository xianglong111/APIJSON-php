<?php
// +----------------------------------------------------------------------
// | JNAPI [ Jinaong Api Docment ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 欧阳 <xianglong111@126.com>
// +----------------------------------------------------------------------

Route::post('get', 'index/home/get')->allowCrossDomain();
Route::post('post', 'index/home/post')->allowCrossDomain();
Route::post('delete', 'index/home/delete')->allowCrossDomain();
Route::post('gets', 'index/home/gets')->allowCrossDomain();
Route::post('posts', 'index/home/posts')->allowCrossDomain();
return [

];

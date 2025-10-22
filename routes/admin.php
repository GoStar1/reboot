<?php

use Illuminate\Support\Facades\Route;

Route::redirect('', '/dashboard/main');
Route::redirect('/', '/dashboard/main');
Route::redirect('home', '/dashboard/main');
Route::group([
    'middleware' => 'auth',
    'prefix' => 'governance',
    'namespace' => 'Governance',
], function () {
    Route::get('template', 'TemplateController@index')->name('template.index');
    Route::get('ava-orders', 'AvaOrderController@index')->name('ava-order.index');
    Route::get('template/edit', 'TemplateController@edit')->name('template.edit');
    Route::post('template/update', 'TemplateController@updateData')->name('template.update');
    Route::get('template/log', 'TemplateController@log')->name('template.log');
    Route::get('template/read', 'TemplateController@read')->name('template.read');
    Route::get('template/write', 'TemplateController@write')->name('template.write');
    Route::get('rpc', 'RpcController@index')->name('rpc.index');
    Route::get('rpc/add', 'RpcController@edit')->name('rpc.add_view');
    Route::any('rpc/edit', 'RpcController@edit')->name('rpc.edit_view');
    Route::any('rpc/update-data', 'RpcController@updateData')->name('rpc.update_data');
    Route::any('rpc/set-order', 'RpcController@setOrder')->name('rpc.set-order');
    Route::any('rpc/set-fixed', 'RpcController@setFixed')->name('rpc.set-fixed');
    Route::any('rpc/gas-price', 'RpcController@gasPrice')->name('rpc.gasPrice');
    Route::get('account', 'AccountController@index')->name('account.index');
    Route::post('account/add', 'AccountController@add')->name('account.add');
    Route::post('account/update-erc20', 'AccountController@updateERC20')->name('account.update-erc20');
    Route::post('account/update-eth', 'AccountController@updateEth')->name('account.update-eth');
    Route::post('account/export', 'AccountController@export')->name('account.export');
    Route::get('account/edit', 'AccountController@edit')->name('account.edit_view');
    Route::post('account/update-data', 'AccountController@updateData')->name('account.update_data');
    Route::get('task', 'TaskController@index')->name('task.index');
    Route::get('task/create', 'TaskController@createView')->name('task.create_view');
    Route::post('task/create', 'TaskController@createData')->name('task.create_data');
    Route::get('task/detail', 'TaskController@detail')->name('task.detail');
    Route::get('task/edit', 'TaskController@edit')->name('task.edit');
    Route::post('task/save', 'TaskController@save')->name('task.save');
    Route::post('task/cancel', 'TaskController@cancelTrans')->name('task.cancel');
    Route::get('task-data', 'TaskDataController@index')->name('task_data.index');
    Route::get('task-data/create', 'TaskDataController@createView')->name('task_data.create_view');
    Route::post('task-data/create', 'TaskDataController@createData')->name('task_data.create_data');
    Route::get('task-data/account', 'TaskDataController@account')->name('task_data.account');
    Route::get('task-data/iframe', 'TaskDataController@iFrame')->name('task_data.iframe');
    Route::get('task-data/abi', 'TaskDataController@abi')->name('task_data.abi');
    Route::get('task-trans', 'TaskTransController@index')->name('task_trans.index');
    Route::get('task-trans/detail', 'TaskTransController@detail')->name('task_trans.detail');
    Route::post('task-trans/save', 'TaskTransController@save')->name('task_trans.save');
    Route::post('task-trans/cancel', 'TaskTransController@cancel')->name('task_trans.cancel');
    Route::get('task-trans/edit', 'TaskTransController@edit')->name('task_trans.edit');
    Route::get('token', 'TokenController@index')->name('token.index');
    Route::get('token/create', 'TokenController@edit')->name('token.create');
    Route::get('token/edit', 'TokenController@edit')->name('token.edit');
    Route::post('token/update', 'TokenController@updateData')->name('token.update');
});
Route::group([
    'middleware' => 'auth',
    'prefix' => 'dashboard',
    'namespace' => 'Dashboard',
], function () {
    Route::get('main', 'MainController@index');
});
Route::group([
    'middleware' => 'auth',
    'prefix' => 'user',
    'namespace' => 'Auth',
], function () {
    Route::get('history', 'UserController@history')->name('user.history');
    Route::get('operation', 'UserController@operation')->name('user.operation');
});

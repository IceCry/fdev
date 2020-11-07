<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/11/7
 * Time: 15:51
 */

use think\facade\Route;

// 获取发短信的key
Route::get('sms_code_key', 'Base/verifyCode')->name('VerifyCode')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//验证码发送
Route::post('sms_code', 'Base/sendSmsCode')->name('SmsCode')
    ->middleware(\app\http\middleware\AllowOriginMiddleware::class);

//小程序支付回调
Route::any('routine/notify', 'wechat.Routine/notify');

//定时任务


//未授权接口
Route::group(function () {

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\http\middleware\AuthTokenMiddleware::class, false);

//已授权接口
Route::group(function () {

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\http\middleware\AuthTokenMiddleware::class, true);


Route::miss(function() {
    if(app()->request->isOptions())
        return \think\Response::create('ok')->code(200)->header([
            'Access-Control-Allow-Origin'   => '*',
            'Access-Control-Allow-Headers'  => 'Authori-zation,Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
            'Access-Control-Allow-Methods'  => 'GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE',
        ]);
    else
        return \think\Response::create()->code(404);
});

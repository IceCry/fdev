<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 11:06
 */

namespace app\admin\controller;

use think\exception\Handle;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class AdminException extends Handle
{
    /**
     * 后台异常
     * @param \think\Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        //参数验证错误
        if($e instanceof ValidateException){
            return app('json')->make(422, $e->getError());
        }
        if ($e instanceof \Exception && request()->isAjax()) {
            return app('json')->fail($e->getMessage(), ['code' => $e->getCode(), 'line' => $e->getLine(), 'message' => $e->getMessage(), 'file' => $e->getFile()]);
        }
        return parent::render($request, $e);
    }
}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/1
 * Time: 9:16
 */

namespace app\http\middleware;

use app\Request;

class PermissionMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $user_id = $request->user_id();

        //判断是否有权限
        if(true){

        }

        return $next($request);
    }
}
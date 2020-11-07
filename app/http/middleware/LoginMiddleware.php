<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/7/4
 * Time: 17:26
 */

namespace app\http\middleware;

class LoginMiddleware
{
    public function handle($request, \Closure $next)
    {
        // 判定用户是否登录
        if(empty(Session::get('user_id'))){
            return redirect(request()->domain().'/login');
        }
        // 继续执行进入到控制器
        return $next($request);
    }
}
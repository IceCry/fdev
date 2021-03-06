<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/3/31
 * Time: 14:28
 */

namespace app\http\middleware;

use app\Request;
use sensen\exceptions\AuthException;
use sensen\interfaces\MiddlewareInterface;
use sensen\repositories\UserRepository;

class AuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next, bool $force=true)
    {
        $authInfo = null;
        $token = trim(ltrim($request->header('Authori-zation'), 'Bearer'));
        if(!$token) $token = trim(ltrim($request->header('Authorization'), 'Bearer'));

        try{
            $authInfo = UserRepository::parseToken($token);
        }catch (AuthException $e){
            if($force){
                return app('json')->make($e->getCode(), $e->getMessage());
            }
        }

        if(!is_null($authInfo)){
            Request::macro('user', function () use (&$authInfo){
                return $authInfo['user'];
            });
            Request::macro('tokenData', function () use (&$authInfo){
                return $authInfo['tokenData'];
            });
        }

        Request::macro('isLogin', function () use (&$authInfo){
            return !is_null($authInfo);
        });
        Request::macro('uid', function () use (&$authInfo){
            return is_null($authInfo) ? 0: $authInfo['user']->id;
        });
        return $next($request);
    }
}
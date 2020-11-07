<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/3/31
 * Time: 14:43
 */

namespace sensen\repositories;

use app\models\user\User;
use app\models\user\UserToken;
use sensen\exceptions\AuthException;

class UserRepository
{
    /**
     * 获取授权信息
     * @param $token
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function parseToken($token): array
    {
        if(!$token || !$tokenData = UserToken::where('token', $token)->find()){
            throw new AuthException('请登录', config('code.un_login'));
        }
        try{
            [$user, $type] = User::parseToken($token);
        }catch (\Throwable $e){
            $tokenData->delete();
            throw new AuthException('登录状态有误,请重新登录', config('exp_login'));
        }

        if(!$user || $user->id != $tokenData->user_id){
            $tokenData->delete();
            throw new AuthException('登录状态有误,请重新登录', config('ch_login'));
        }

        $tokenData->type = $type;
        return compact('user', 'tokenData');
    }
}
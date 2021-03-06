<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/8
 * Time: 17:08
 */

namespace app\api\controller\wechat;

use app\api\controller\Base;
use app\models\user\User;
use app\models\user\UserToken;
use app\Request;
use sensen\services\RoutineService;
use sensen\services\UtilService;
use think\facade\Cache;
use think\facade\Db;

class Routine extends Base
{
    /**
     * 授权登录
     * @param Request $request
     * @return mixed
     */
    public function auth(Request $request)
    {
        $cache_key = '';
        list($code, $post_cache_key, $login_type) = UtilService::postMore([
            ['code', ''],
            ['cache_key', ''],
            ['login_type', '']
        ], $request, true);

        $session_key = Cache::get('ss_api_code_' . $post_cache_key);
        if (!$code && !$session_key)
            return app('json')->fail('授权失败,参数有误');
        if ($code && !$session_key) {
            try {
                $userInfoCong = RoutineService::getUserInfo($code);
                $session_key = $userInfoCong['session_key'];
                $cache_key = md5(time() . $code);
                Cache::set('ss_api_code_' . $cache_key, $session_key, 86400);
            } catch (\Exception $e) {
                return app('json')->fail('获取session_key失败，请检查您的配置！', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
            }
        }

        $data = UtilService::postMore([
            ['spread_spid', 0],
            ['spread_code', ''],
            ['iv', ''],
            ['encryptedData', ''],
        ]);//获取前台传的code
        try {
            //解密获取用户信息
            $userInfo = RoutineService::encryptor($session_key, $data['iv'], $data['encryptedData']);
        } catch (\Exception $e) {
            if ($e->getCode() == '-41003') return app('json')->fail('获取会话密匙失败');
        }
        if (!isset($userInfo['openId'])) return app('json')->fail('openid获取失败');
        if (!isset($userInfo['unionId'])) $userInfo['unionId'] = '';
        $userInfo['spid'] = $data['spread_spid'];
        $userInfo['code'] = $data['spread_code'];
        $userInfo['session_key'] = $session_key;
        $userInfo['login_type'] = $login_type;


        $uid = User::routineOauth($userInfo);

        $userInfo = User::where('id', $uid)->find();
        $token = UserToken::createToken($userInfo, 'routine');

        if ($token) {
            event('UserLogin', [$userInfo, $token]);
            return app('json')->successful('登陆成功！', [
                'token' => $token->token,
                'userInfo' => $userInfo,
                'expires_time' => strtotime($token->expires_time),
                'cache_key' => $cache_key
            ]);
        } else
            return app('json')->fail('获取用户访问token失败!');
    }

    /**
     * 获取小程序订阅消息id
     * @return mixed
     */
    public function getTpls()
    {
        $tplIds = $this->getDataValue('subscribe_tpl');

        //处理数组为 ['make_answer'=>'tpl_id', ...]
        $tplArr = [];
        foreach ($tplIds as $v){
            $tplArr[$v['var']] = $v['tpl_id'];
        }
        return app('json')->success($tplArr);
    }



}
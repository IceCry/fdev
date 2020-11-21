<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 11:07
 */

namespace app\admin\controller;

use app\admin\model\system\Admin;
use sensen\services\UtilService;
use think\facade\Route;
use think\facade\Session;

class Login extends Base
{
    public function index()
    {
        return $this->fetch('login/index');
    }

    /**
     * 验证码
     */
    public function captcha()
    {
        ob_clean();
        return captcha();
    }

    /**
     * 登录验证
     * @return mixed
     */
    public function verify()
    {
        if(!request()->isPost()) return $this->failed('请登录！');
        list($account, $password, $code, $remember) = UtilService::postMore([
            'username', 'password', 'code', 'remember'
        ], null, true);

        $remember = $remember?1:0;

        //校验验证码
        if(!captcha_check($code)){
            return $this->failed("验证码错误");
        }

        //限制登录次数
        $error = Session::get('login_error') ?: ['num'=>0, 'time'=>time()];
        $time = "-".config('web.login_try_later')." minutes";
        if($error['num'] >= config('web.login_try_times') && $error['time'] > strtotime($time)){
            return $this->failed("错误次数过多，请稍后再试");
        }
        //登录检测
        $res = Admin::login($account, $password, $remember);
        if ($res) {
            Session::set('login_error', null);
            Session::save();
            insert_log('登录成功', 'login/verify', 1, $account, 0, '');
            return $this->successful(['url' => Route::buildUrl('Index/index')->build()]);
        } else {
            $error['num'] += 1;
            $error['time'] = time();
            Session::set('login_error', $error);
            Session::save();
            insert_log('登录失败', 'login/verify', 6, $account, 0, '');
            return $this->failed(Admin::getErrorInfo('用户名错误，请重新输入'));
        }
    }

    /**
     * 退出登陆
     */
    public function logout()
    {
        Admin::clearLoginInfo();
        $this->redirect(Route::buildUrl('login/index')->build());
    }

}
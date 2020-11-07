<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/1
 * Time: 14:24
 */

namespace app\http\validates;

use think\Validate;

class RegisterValidates extends Validate
{
    protected $regex = [ 'phone' => '/^1[3456789]\d{9}$/'];

    protected $rule = [
        'phone'  =>  'require|regex:phone',
        'account'  =>  'require|regex:phone',
        'captcha'  =>  'require|length:6',
        'password'  =>  'require',
    ];

    protected $message  =   [
        'phone.require'     =>  '手机号必须填写',
        'phone.regex'       =>  '手机号格式错误',
        'account.require'   =>  '手机号必须填写',
        'account.regex'     =>  '手机号格式错误',
        'captcha.require'   =>  '验证码必须填写',
        'captcha.length'    =>  '验证码不能超过6个字符',
        'password.require'  =>  '密码必须填写',
    ];

    /**
     * 验证码场景
     * @return RegisterValidates
     */
    public function sceneCode()
    {
        return $this->only(['phone']);
    }

    /**
     * 注册场景
     * @return RegisterValidates
     */
    public function sceneRegister()
    {
        return $this->only(['account','captcha','password']);
    }

}
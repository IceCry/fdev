<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/13
 * Time: 10:22
 */

namespace app\admin\model\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Session;
use think\model\concern\SoftDelete;

class Admin extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public function getRoleStrAttr($value, $data)
    {
        $roles = Db::name('auth_group')->where('id', 'in', $data['role_ids'])->column('title');
        return implode('、', $roles);
    }

    public function getLastTimeAttr($value)
    {
        return $value?date('Y/m/d H:i:s', $value):'';
    }

    /**
     * 登录操作
     * @param $account
     * @param $password
     * @param $remember
     * @return bool
     */
    public static function login($account, $password, $remember)
    {
        $info = self::get(compact('account'));
        if(!$info || ($info['password'] != en_pwd($password, $info['salt']))){
            return self::setErrorInfo('帐号或密码错误');
        }
        if($info['status']!=1 || $info['delete_time']>0){
            return self::setErrorInfo('帐号已禁止登录');
        }

        //卸载重要信息
        unset($info['password'], $info['salt'], $info['create_time'], $info['update_time'], $info['delete_time'], $info['remark']);

        self::setLoginInfo($info, $remember);

        event('UserLoginAfter', [$info]);
        return true;
    }

    /**
     * 设置登录信息
     * 当前使用加密cookie
     * @param $info
     * @param $remember
     */
    public static function setLoginInfo($info, $remember)
    {
        $userId = auth_code($info['id'], 'ENCODE', 0);
        $account = auth_code($info['account'], 'ENCODE', 0);

        if($remember){
            $expireTime = config('web.cookie_expire');
            Cookie::set('user_id', $userId, $expireTime);
            Cookie::set('account', $account, $expireTime);
            Cookie::save();
        }else{
            Cookie::set('user_id', $userId);
            Cookie::set('account', $account);
            Cookie::save();
        }

        //保存用户信息到session中
        Session::set('userInfo', $info->toArray());
        Session::save();
    }

    /**
     * 判断是否已登录
     * @return mixed
     */
    public static function isOnline()
    {
        return Cookie::has('user_id') && Cookie::has('account');
    }

    /**
     * 清除登录信息
     */
    public static function clearLoginInfo()
    {
        Cookie::delete('user_id');
        Cookie::delete('account');
        Cookie::save();

        Session::delete('userInfo');
        Session::save();
    }

    /**
     * 获取最新用户信息
     */
    public static function getUserInfoOrFail()
    {
        $uid = Cookie::get('user_id');
        $account = Cookie::get('account');
        if (!$uid || !$account) exception('请登陆');

        //解密cookie
        $uid = auth_code($uid, 'DECODE', 0);
        $account = auth_code($account, 'DECODE', 0);
        $info = self::where(['id'=>$uid, 'account'=>$account])->withoutField('password, salt, create_time, update_time, delete_time, remark')->find();
        if(!$info) exception('请登录!');
        if (!$info['status']) exception('该账号已被禁用!');
        return $info;
    }

    /**
     * 获取用户对应权限
     * 写缓存需区分用户
     */
    public static function getUserAuth($userInfo=[])
    {
        if(in_array($userInfo['id'], config('web.super_admin'))){
            return Role::getAllRule();
        }else{
            $roles = Db::name('auth_group_access')->where('uid', $userInfo['id'])->column('group_id');
            $roles = implode(',', $roles);
            return Role::getUserRule($roles, $userInfo['id']);
        }
    }

    /**
     * 获取用户菜单
     * @param int $uid
     * @return array
     */
    public static function getUserMenu($uid=0)
    {
        if(in_array($uid, config('web.super_admin'))){
            return Role::getAllRule(true);
        }else{
            $roles = Db::name('auth_group_access')->where('uid', $uid)->column('group_id');
            $roles = implode(',', $roles);
            return Role::getUserRule($roles, $uid, true);
        }
    }

    /**
     * 获取管理员数据
     * @param $where
     * @return array
     */
    public static function getAdminData($where)
    {
        $model = new self();
        $keyword = trim($where['keyword']);
        if($keyword){
            $model = $model->where('name|phone', 'like', "%$keyword%");
        }
        $count = $model->count();
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        $data = $model->append(['role_str'])->select();
        return compact('count', 'data');
    }

}
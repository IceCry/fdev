<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 13:52
 */

namespace sensen\subscribes;


use app\admin\model\system\Admin;

class UserSubscribe
{
    public function handle()
    {

    }

    /**
     * 用户登录
     * @param $event
     */
    public function onUserLogin($event)
    {

    }

    /**
     * 添加管理员最后登录时间和ip
     * @param $event
     */
    public function onUserLoginAfter($event)
    {
        list($info) = $event;
        Admin::edit(['last_ip'=>request()->ip(), 'last_time' => time()], $info['id']);
    }

}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/13
 * Time: 11:18
 */

namespace sensen\subscribes;


class SystemSubscribe
{
    public function handle()
    {

    }

    /**
     * 添加管理员访问记录
     * @param $event
     */
    public function onAdminVisit($event)
    {
        list($adminInfo) = $event;

    }

    /**
     * 用户登录后执行操作
     * @param $event
     */
    public function onUserLoginAfter($event)
    {

    }

}
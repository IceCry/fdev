<?php


namespace sensen\listeners\user;

use sensen\interfaces\ListenerInterface;

class UserLogin implements ListenerInterface
{
    /**
     * 用户成功登录后
     * @param $event
     */
    public function handle($event): void
    {
        [$user, $token] = $event;

    }
}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/11/7
 * Time: 17:19
 */

namespace sensen\interfaces;

use think\queue\Job;

interface JobInterface
{
    public function fire(Job $job, $data): void;
}
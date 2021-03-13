<?php
/**
 * Desc: 测试job
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/2
 * Time: 14:48
 */

namespace sensen\jobs;

use sensen\basic\BaseJob;

class TestJob extends BaseJob
{
    public function doJob($id)
    {
        file_put_contents('./public/test.txt', time().PHP_EOL, FILE_APPEND);
        return true;
    }
}
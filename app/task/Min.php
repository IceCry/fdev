<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/11
 * Time: 16:27
 */

namespace app\task;

use yunwuxin\cron\Task;

class Min extends Task
{
    public function configure()
    {
        $this->everyMinute();
    }

    /**
     * 执行任务
     * @return mixed
     */
    protected function execute()
    {
        file_put_contents('test.txt', time().PHP_EOL, FILE_APPEND);
    }
}
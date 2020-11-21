<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/10/22
 * Time: 10:14
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use sensen\services\UtilService;
use app\models\system\Log as LogModel;

class Log extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        $where = UtilService::getMore([
            ['is_admin', ''],
            ['keyword', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(LogModel::getLogData($where));
    }
}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/10/12
 * Time: 14:57
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use app\models\system\Express as ExpressModel;
use sensen\services\UtilService;

class Express extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        $where = UtilService::getMore([
            ['keyword', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(ExpressModel::getExpressData($where));
    }
}
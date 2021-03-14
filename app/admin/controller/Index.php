<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 11:08
 */

namespace app\admin\controller;

use app\models\order\Order;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\auction\Deposit;

class Index extends AuthController
{
    /**
     * 基础框架
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $menu = $this->getMenu();
        $this->assign(['menu'=>$menu]);
        return $this->fetch();
    }

    /**
     * 首页
     * @return string
     * @throws \Exception
     */
    public function home()
    {
        return $this->fetch();
    }
}
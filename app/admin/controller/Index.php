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
        //订单总数 拍品+藏品
        $allOrderCount = Order::orderCount();
        //拍品
        $collectionOrderCount = Order::orderCount(0, 1);
        //商品
        $goodsOrderCount = Order::orderCount(0, 2);

        //订单金额 拍品+藏品
        $allOrderPrice = Order::orderPrice();
        //拍品
        $collectionOrderPrice = Order::orderPrice(0, 1);
        //商品
        $goodsOrderPrice = Order::orderPrice(0, 2);

        //保证金充值 不含系统后台充值金额
        $depositRecharge = UserBill::countBillPrice(1, 'deposit', 'recharge');

        //保证金抵扣
        $depositDeduction = UserBill::countBillPrice(0, 'deposit', 'deduction');

        //保证金余额 含冻结
        $freezeDeposit = Deposit::getFreezeDeposit();
        $depositLeft = User::sum('deposit_price');
        //保证金扣除
        $deductDeposit = UserBill::countBillPrice(0, 'deposit', 'system');
        //系统充值保证金
        $systemRechargeDeposit = UserBill::countBillPrice(1, 'deposit', 'system');

        //拍卖
        //出价次数
        //流拍数
        //成交数

        $this->assign(compact('allOrderCount', 'collectionOrderCount', 'goodsOrderCount', 'allOrderPrice', 'collectionOrderPrice', 'goodsOrderPrice', 'depositRecharge', 'depositDeduction', 'freezeDeposit', 'depositLeft', 'deductDeposit', 'systemRechargeDeposit'));

        return $this->fetch();
    }
}
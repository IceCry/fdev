<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/22
 * Time: 15:45
 */
namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\models\user\User as UserModel;
use sensen\services\JsonService;
use sensen\services\UtilService;

class User extends AuthController
{
    public function index()
    {
        $province = $this->getCityData(0);
        $this->assign('province', $province);
        return $this->fetch();
    }

    /**
     * 获取数据
     */
    public function getData()
    {
        $where = UtilService::getMore([
            ['province', ''],
            ['city', ''],
            ['district', ''],
            ['keyword', ''],
            ['page', 1],
            ['limit', 20],
            ['excel', 0],
            ['role', 0],
            ['field', ''],
            ['order', '']
        ]);
        return JsonService::successlayui(UserModel::getDataList($where));
    }

    /**
     * 用户详情
     * @param int $user_id
     * @return string
     * @throws \Exception
     */
    public function info($user_id=0)
    {
        //获取基础信息
        $info = UserModel::get($user_id)->toArray();
        $this->assign(['info'=>$info]);
        return $this->fetch();
    }

    /**
     * 获取xmSelect数据
     * @param string $keyword
     * @param integer $special_id
     */
    public static function getXmData($keyword='', $special_id=0)
    {
        $data = UserModel::getXmData($keyword, $special_id);

        return JsonService::success('ok', $data);
    }

}
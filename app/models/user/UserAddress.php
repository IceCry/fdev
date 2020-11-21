<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/15
 * Time: 13:50
 */

namespace app\models\user;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class UserAddress extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public function getAreaCodeAttr($value)
    {
        return $value?json_decode($value, true):[11, 1101, 110101];
    }

    /**
     * 设置默认收货地址
     * @param $id 地址id
     * @param $uid 用户uid
     * @return bool
     */
    public static function setDefaultAddress($id, $uid)
    {
        self::beginTrans();
        $res1 = self::where('user_id',$uid)->update(['is_default'=>0]);
        $res2 = self::where(['id'=>$id, 'user_id'=>$uid])->update(['is_default'=>1]);
        $res =$res1 !== false && $res2 !== false;
        self::checkTrans($res);
        return $res;
    }

}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/30
 * Time: 11:29
 */

namespace app\models\user;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class UserBill extends BaseModel
{
    use ModelTrait;

    /**
     * 收入
     * @param $title
     * @param $user_id
     * @param $category
     * @param $type
     * @param $number
     * @param int $link_id
     * @param int $balance
     * @param string $mark
     * @param int $status
     * @return UserBill|\think\Model
     */
    public static function income($title, $user_id, $category, $type, $number, $link_id = 0, $balance = 0, $mark = '', $status = 1)
    {
        $pm = 1;
        return self::create(compact('title', 'user_id', 'link_id', 'category', 'type', 'number', 'balance', 'mark', 'status', 'pm'));
    }

    /**
     * 支出
     * @param $title
     * @param $user_id
     * @param $category
     * @param $type
     * @param $number
     * @param int $link_id
     * @param int $balance
     * @param string $mark
     * @param int $status
     * @return UserBill|\think\Model
     */
    public static function expend($title, $user_id, $category, $type, $number, $link_id = 0, $balance = 0, $mark = '', $status = 1)
    {
        $pm = 0;
        return self::create(compact('title', 'user_id', 'link_id', 'category', 'type', 'number', 'balance', 'mark', 'status', 'pm'));
    }

    /**
     * 获取用户账单信息
     * @param $where
     * @return \think\Collection
     */
    public static function getUserBillData($where)
    {
        $model = new self();
        if(isset($where['user_id']) && $where['user_id']){
            $model = $model->where('user_id', $where['user_id']);
        }
        if(isset($where['pm']) && $where['pm']){
            $model = $model->where('pm', $where['pm']);
        }
        if(isset($where['category']) && $where['category']){
            $model = $model->where('category', $where['category']);
        }
        if(isset($where['type']) && $where['type']){
            $model = $model->where('type', $where['type']);
        }
        $model = $model->where('status', 1);
        $count = $model->count();
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        $data = $model->order('id desc')->select();
        return compact('count', 'data');
    }

    /**
     * 获取用户账单信息
     * @param $where
     * @return \think\Collection
     */
    public static function apiUserBillData($where)
    {
        $model = new self();
        if(isset($where['user_id']) && $where['user_id']){
            $model = $model->where('user_id', $where['user_id']);
        }
        if(isset($where['pm']) && $where['pm']){
            $model = $model->where('pm', $where['pm']);
        }
        if(isset($where['category']) && $where['category']){
            $model = $model->where('category', $where['category']);
        }
        if(isset($where['type']) && $where['type']){
            $model = $model->where('type', $where['type']);
        }
        $model = $model->where('status', 1);
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        return $model->order('id desc')->select();
    }

    /**
     * 计算bill金额
     * @param $pm
     * @param $category
     * @param $type
     * @param int $user_id
     * @return array
     */
    public static function countBillPrice($pm, $category, $type, $user_id=0)
    {
        $where = ['status'=>1];
        if($user_id>0){ $where['user_id'] = $user_id; }
        $where['pm'] = $pm;
        $where['category'] = $category;
        $where['type'] = $type;

        $count = self::where($where)->count();
        $price = self::where($where)->sum('number');

        return compact('count', 'price');
    }

}
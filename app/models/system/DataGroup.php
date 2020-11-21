<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/8
 * Time: 15:53
 */

namespace app\models\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class DataGroup extends BaseModel
{
    use ModelTrait;

    /**
     * 获取列表
     * @return array
     */
    public static function getDataGroupData($where=[])
    {
        $model = new self;
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $list = $model->select();
        $data = count($list) ? $list->toArray() : [];

        $count = self::count();
        return compact('count', 'data');
    }

    /**
     * 获取参数
     * @param $id
     * @return array
     */
    public static function getFields($id){
        $fields = json_decode(self::where('id',$id)->value("fields"),true)?:[];
        return compact('fields');
    }


}
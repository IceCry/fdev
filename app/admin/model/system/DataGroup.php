<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 16:08
 */

namespace app\admin\model\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class DataGroup extends BaseModel
{
    use ModelTrait;

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public static function getDataGroupData($where)
    {
        $model = new self;
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $list = $model->order('id desc')->select();
        $data = count($list) ? $list->toArray() : [];

        $count = self::count();
        return compact('count', 'data');
    }

    /**
     * 获取参数
     * @param $id
     * @return array
     */
    public static function getFields($id)
    {
        $fields = json_decode(self::where('id',$id)->value("fields"),true)?:[];
        //追加是否启用选项
        $status = [
            'name'=>'是否启用',
            'var'=>'status',
            'type'=>'number',
            'param'=>'1'
        ];
        array_push($fields, $status);

        return compact('fields');
    }
}
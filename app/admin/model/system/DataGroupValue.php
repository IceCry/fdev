<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 16:29
 */

namespace app\admin\model\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class DataGroupValue extends BaseModel
{
    use ModelTrait;

    /**
     * 获取列表
     * @return array
     */
    public static function getDataGroupValueData()
    {
        $model = new self;
        $list = $model->select();
        $data = count($list) ? $list->toArray() : [];
        $count = self::count();
        return compact('count', 'data');
    }

    /**
     * 根据where条件获取当前表中的前20条数据
     * @param $params
     * @return array
     */
    public static function getList($params)
    {
        $model = new self;
        if ($params['gid'] !== '') $model = $model->where('gid', $params['gid']);
        $model = $model->order('sort desc,id ASC');
        return self::page($model, function ($item, $key) {
            $info = json_decode($item->value, true);
            //todo 区分不同数据类型
            /*foreach ($info as $index => $value) {
                if ($value["type"] == "checkbox") $info[$index]["value"] = implode(",", $value["value"]);
            }*/
            $item->value = $info;
        }, [], 100);
    }

}
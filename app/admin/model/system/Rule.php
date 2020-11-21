<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 11:52
 */

namespace app\admin\model\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class Rule extends BaseModel
{
    use ModelTrait;

    protected $name = 'auth_rule';

    /**
     * 转小写
     */
    public function setNameAttr($value)
    {
        return strtolower($value);
    }

    /**
     * 条件
     * @param $value
     * @return string
     */
    public function setConditionAttr($value)
    {
        $value = $value ? explode('/', $value) : [];
        $params = array_chunk($value, 2);
        $data = [];
        foreach ($params as $param) {
            if (isset($param[0]) && isset($param[1])) $data[$param[0]] = $param[1];
        }
        return json_encode($data);
    }

    public function getConditionAttr()
    {

    }

    /**
     * 获取权限数据
     * @return array
     */
    public static function getRuleData()
    {
        $model = new self;
        $data = $model->order('sort_num desc')->select();
        $count = count($data);
        return compact('count', 'data');
    }

}
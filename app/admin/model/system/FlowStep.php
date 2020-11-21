<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/14
 * Time: 17:27
 */

namespace app\admin\model\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class FlowStep extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    public function getOutConditionAttr($value)
    {
        return $value?json_decode($value):"";
    }

}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/14
 * Time: 14:56
 */

namespace app\admin\model\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class Flow extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 获取流程数据
     * @return array
     */
    public static function getFlowData()
    {
        $model = new self;
        $data = $model->select();
        $count = count($data);
        return compact('count', 'data');
    }

    /**
     * 获取指定流程步骤
     * @param $id
     */
    public static function getFlowStepData($id)
    {
        $data = FlowStep::where(['flow_id'=>$id])->select();
        $count = count($data);
        return compact('count', 'data');
    }

}
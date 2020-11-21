<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/10/12
 * Time: 10:52
 */

namespace app\models\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class Express extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public static function getExpressData($where=[])
    {
        $model = new self();
        $keyword = trim($where['keyword']);
        if($keyword){
            $model = $model->where('name', 'like', "%$keyword%");
        }
        $count = $model->count();
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        $data = $model->order('sort_num desc, id desc')->select();
        return compact('count', 'data');
    }

}
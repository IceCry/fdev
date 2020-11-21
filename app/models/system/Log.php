<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/10/22
 * Time: 10:16
 */

namespace app\models\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class Log extends BaseModel
{
    use ModelTrait;

    public function getLevelStrAttr($value, $data)
    {
        return get_log_level($data['level']);
    }

    /**
     * 获取日志记录
     * @param array $where
     * @return array
     */
    public static function getLogData($where=[])
    {
        $model = new self();
        if(isset($where['is_admin']) && $where['is_admin']!=''){
            $model = $model->where('is_admin', $where['is_admin']);
        }
        $keyword = trim($where['keyword']);
        if($keyword){
            $model = $model->where('message', 'like', "%$keyword%");
        }
        $count = $model->count();
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        $data = $model->append(['level_str'])->order('id desc')->select();
        return compact('count', 'data');
    }
}
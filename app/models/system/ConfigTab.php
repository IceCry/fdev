<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:49
 */

namespace app\models\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class ConfigTab extends BaseModel
{
    use ModelTrait;

    /**
     * 获取配置分类
     * @return array
     */
    public static function getConfigTabData()
    {
        $data = self::select();
        $count = count($data);
        return compact('count', 'data');
    }

    /**
     * 获取子级
     * @param $pid
     */
    public static function getAllTab($pid=0)
    {
        if($pid){
            return self::where(['pid'=>$pid, 'status'=>1])->select();
        }else{
            return self::where(['status'=>1])->select();
        }
    }

}
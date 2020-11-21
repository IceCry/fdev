<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/27
 * Time: 11:00
 */

namespace app\models\system;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class SmsHistory extends BaseModel
{
    use ModelTrait;

    /**
     * 插入一条记录
     * @param $phone
     * @param $template
     * @param $mtable
     * @param $mid
     * @param int $user_id
     * @param string $user_name
     * @param int $num
     * @return SmsHistory|\think\Model
     */
    public static function insertOne($phone, $template, $mtable, $mid, $user_id=0, $user_name='', $num=1)
    {
        return self::create([
            'user_id'=>$user_id,
            'user_name'=>$user_name,
            'phone'=>$phone,
            'template'=>$template,
            'create_time'=>time(),
            'create_ip'=>request()->ip(),
            'num'=>$num,
            'mtable'=>$mtable,
            'mid'=>$mid
        ]);
    }
}
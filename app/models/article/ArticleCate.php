<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/1
 * Time: 15:24
 */

namespace app\models\article;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class ArticleCate extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 获取文章分类
     */
    public static function getArticleCateData()
    {
        $data = self::select();
        $count = count($data);
        return compact('count', 'data');
    }

}
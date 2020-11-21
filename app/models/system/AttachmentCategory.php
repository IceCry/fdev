<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/8
 * Time: 16:58
 */

namespace app\models\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class AttachmentCategory extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

}
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/2
 * Time: 16:24
 */

namespace app\models\system;

use sensen\traits\ModelTrait;
use sensen\basic\BaseModel;

class Attachment extends BaseModel
{
    use ModelTrait;

    /**
     * 添加附件
     * @param string $type
     * @param string $name
     * @param string $name_origin
     * @param string $path
     * @param string $ext
     * @param string $mime
     * @param int $size
     * @param string $path_zip
     * @param string $mid
     * @param int $cate_id
     * @param int $user_id
     * @param string $user_name
     * @param string $hash
     * @param int $upload_type
     * @return Attachment|\think\Model
     */
    public static function attachmentAdd($type='', $name='', $name_origin='', $path='', $ext='', $mime='', $size=0, $path_zip = '', $mid='', $cate_id=0, $user_id=0, $user_name='', $mtable='', $hash='', $upload_type=1)
    {
        $data['type'] = $type;
        $data['name'] = $name;
        $data['name_origin'] = $name_origin;
        $data['path'] = $path;
        $data['ext'] = $ext;
        $data['mime'] = $mime;
        $data['size'] = $size;
        $data['path_zip'] = $path_zip;
        $data['mid'] = $mid;
        $data['cate_id'] = $cate_id;
        $data['user_id'] = $user_id;
        $data['user_name'] = $user_name;
        $data['upload_type'] = $upload_type;
        $data['uuid'] = create_uuid();
        $data['mtable'] = $mtable;
        $data['hash'] = $hash;
        $data['create_time'] = time();
        return self::create($data);
    }

    /**
     * 获取分类图
     * @param $id
     * @return array
     */
    public static function getAll($id)
    {
        $model = new self;
        $where['cate_id'] = $id;
        $where['type'] = 'image';
        $model->where($where)->order('id desc');
        return $model->page($model, $where, '', 24);
    }

    /**
     * 获取图片列表
     * @param $where
     * @return array
     */
    public static function getImageList($where)
    {
        $model = new self;
        $model = $model->where(['type'=>'image', 'delete_time'=>0]);
        if (isset($where['cate_id']) && $where['cate_id']) {
            $model = $model->where('cate_id', $where['cate_id']);
        }
        $count = $model->count();
        $model = $model->page((int)$where['page'], (int)$where['limit']);
        $model = $model->order('id desc');
        $list = $model->select();
        $list = count($list) ? $list->toArray() : [];
        $site_url = sys_config('site_url');
        foreach ($list as &$item) {
            if ($site_url) {
                $item['path_zip'] = set_web_url($item['path_zip']);
                $item['path'] = set_web_url($item['path']);
            }
        }
        return compact('list', 'count');
    }

    /**
     * 获取单条信息
     * @param $value
     * @param string $field
     * @return array
     */
    public static function getInfo($value, $field = 'id')
    {
        $where[$field] = $value;
        $count = self::where($where)->count();
        if (!$count) return false;
        return self::where($where)->find()->toArray();
    }
}
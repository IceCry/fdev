<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:38
 */

namespace app\models\article;

use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\model\concern\SoftDelete;

class Article extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 关联文章内容
     */
    public function content()
    {
        return $this->hasOne(ArticleContent::class)->bind(['content']);
    }

    /**
     * 获取文章类型
     * @param $value
     * @return string
     */
    public function getTypeStrAttr($value, $data)
    {
        $str = '';
        $types = ArticleCate::select();
        foreach ($types as $k=>$v){
            if($v['id']==$data['cate_id']){
                $str = $types[$k]['name'];
                break;
            }
        }
        return $str;
    }

    /**
     * 获取指定文章
     * @param $where
     */
    public static function getArticleData($where=[])
    {
        $model = new self();
        if($where['cate_id']){
            $model = $model->where('cate_id', $where['cate_id']);
        }
        $keyword = trim($where['keyword']);
        if($keyword){
            $model = $model->where('title|intro', 'like', "%$keyword%");
        }
        $count = $model->count();
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        $data = $model->append(['type_str'])->order('sort_num desc, id desc')->select();
        return compact('count', 'data');
    }

    /**
     * 获取指定文章
     * @param $where
     */
    public static function apiArticleData($where=[])
    {
        $model = new self();
        if($where['cate_id']){
            $model = $model->where('cate_id', $where['cate_id']);
        }
        $keyword = trim($where['keyword']);
        if($keyword){
            $model = $model->where('title|intro', 'like', "%$keyword%");
        }
        $model = $model->where('status', 1);
        if ($where['page']) $model = $model->page($where['page'], $where['limit']);
        return $model->append(['type_str'])->order('sort_num desc, id desc')->select();
    }

    /**
     * 插入文章附表
     * @param $id
     * @param $content
     * @return ArticleContent|bool|int|string
     */
    public static function setContent($id, $content)
    {
        $count = ArticleContent::where('article_id', $id)->count();
        $data['article_id'] = $id;
        $data['content'] = $content;
        if ($count) {
            $contentSql = ArticleContent::where('article_id', $id)->value('content');
            if ($contentSql == $content){
                $res = true;
            }else{
                $res = ArticleContent::where('article_id', $id)->update(['content' => $content]);
            }
            if ($res !== false) $res = true;
        } else {
            $res = ArticleContent::insert($data);
        }
        return $res;
    }
}
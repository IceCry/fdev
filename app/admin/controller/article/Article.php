<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:37
 */

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use sensen\services\UtilService;
use app\models\article\Article as ArticleModel;
use app\models\article\ArticleCate;

class Article extends AuthController
{
    public function index()
    {
        //获取文章分类
        $cates = ArticleCate::select();

        $this->assign(['cates'=>$cates]);
        return $this->fetch();
    }

    public function getData()
    {
        $where = UtilService::getMore([
            ['cate_id', ''],
            ['keyword', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(ArticleModel::getArticleData($where));
    }

    public function create()
    {
        //获取文章分类
        $cates = ArticleCate::select();

        $this->assign(['cates'=>$cates]);
        return $this->fetch();
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['title', ''],
            ['intro', ''],
            ['thumb', ''],
            ['cate_id', ''],
            ['content', ''],
            ['attach_ids', ''],
            ['create_time', ''],
        ]);
        $id = $data['id'];
        unset($data['id']);

        //处理content
        $content = $data['content'];
        unset($data['content']);
        //暂不移除链接
        //$content = remove_link($content);

        if(!$data['intro']){
            $data['intro'] = msubstr(filter_data($content), 0, 225, 'utf-8', false);
            $data['intro'] = str_replace('nbsp;','',str_replace('&amp;','',str_replace('&nbsp;','',$data['intro'])));
        }

        $data['create_time'] = strtotime($data['create_time']);

        $res = false;
        if($id){
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data, $id, 'id');
            //更新内容表
            $res2 = ArticleModel::setContent($id, $content);
            if($res1 && $res2){
                $res = true;
            }
            ArticleModel::checkTrans($res);
        }else{
            $data['uuid'] = create_uuid();
            $data['user_id'] = $this->uid;
            $data['click'] = mt_rand(100, 600);
            $data['user_name'] = $this->userInfo['name'];

            ArticleModel::beginTrans();
            $res1 = ArticleModel::create($data);
            $res2 = false;
            if($res1){
                $res2 = ArticleModel::setContent($res1->id, $content);
            }
            if($res1 && $res2){
                $res = true;
            }
            ArticleModel::checkTrans($res);
        }
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    public function info($id=0)
    {
        $info = ArticleModel::with('content')->find($id);
        return JsonService::success('ok', $info);
    }

    public function detail($id=0)
    {
        $info = ArticleModel::with('content')->find($id);
        if($info['delete_time']){
            $info = [];
        }
        ArticleModel::bcInc($id, 'click', 1, 'id');
        $this->assign(['info'=>$info]);
        return $this->fetch('public/article');
    }

    public function delete($id=0)
    {
        $res = ArticleModel::del($id);
        if($res){
            insert_log('删除文章', 'article/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }



}
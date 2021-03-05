<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/1
 * Time: 15:33
 */

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use app\models\article\ArticleCate as ArticleCateModel;
use sensen\services\UtilService;

class ArticleCate extends AuthController
{
    /**
     * 文章分类
     * 暂仅支持一级分类
     */
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        return JsonService::successlayui(ArticleCateModel::getArticleCateData());
    }

    public function create()
    {
        return $this->fetch();
    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['pid', 0],
            ['name', ''],
            ['en_title', ''],
            ['sort_num', 0],
            ['icon', '']
        ]);
        $id = $data['id'];
        unset($data['id']);

        if($id){
            $res = ArticleCateModel::edit($data, $id, 'id');
        }else{
            $res = ArticleCateModel::create($data);
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    public function info($id=0)
    {
        $info = ArticleCateModel::get($id);
        return JsonService::success('ok', $info);
    }

    public function delete($id=0)
    {
        $res = ArticleCateModel::del($id);
        if($res){
            insert_log('删除文章分类', 'articleCate/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

}
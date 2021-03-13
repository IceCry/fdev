<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/1
 * Time: 15:33
 */

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use FormBuilder\Factory\Elm;
use sensen\services\JsonService;
use app\models\article\ArticleCate as ArticleCateModel;
use sensen\services\UtilService;
use think\facade\Route;

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

    public function create($id=0, $pid=0)
    {
        if($id>0){
            $info = ArticleCateModel::get($id);
            $name = $info['name'];
            $icon = $info['icon'];
            $sort_num = $info['sort_num'];
            $status = intval($info['status']);
        }else{
            $name = $icon = '';
            $sort_num = 0;
            $status = 1;
        }
        $name = Elm::input('name', '分类名称', $name)->required()->maxlength(45);
        //$icon = Elm::frameImage('icon', '分类图标', Route::buildUrl('widget.attach/index'), $icon);
        $icon = Elm::uploadImage('icon', '分类图标', Route::buildUrl('widget.attach/upload'), $icon);
        $sortNum = Elm::number('sort_num', '排序值', $sort_num);
        $status = Elm::radio('status', '状态', $status);
        $status->options(function(){
            $options = [['value'=>1, 'label'=>'显示'], ['value'=>0, 'label'=>'禁用']];
            return $options;
        });
        $pid = Elm::hidden('pid', $pid);
        $id = Elm::hidden('id', $id);
        $form = Elm::createForm(Route::buildUrl('save'))->setMethod('POST');
        $form->setRule([$name, $icon, $sortNum, $status, $pid, $id]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
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
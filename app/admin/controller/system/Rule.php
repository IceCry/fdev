<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 13:57
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use FormBuilder\Factory\Elm;
use sensen\services\JsonService;
use app\admin\model\system\Rule as RuleModel;
use sensen\services\UtilService;
use think\facade\Route;

class Rule extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取权限列表数据
     */
    public function getData()
    {
        return JsonService::successlayui(RuleModel::getRuleData());
    }


    public function create($id=0, $pid=0)
    {
        if($id>0){
            $info = RuleModel::get($id);
            $title = $info['title'];
            $name = $info['name'];
            $ico = $info['ico'];
            $is_check = intval($info['is_check']);
            $sort_num = intval($info['sort_num']);
            $is_menu = intval($info['is_menu']);
        }else{
            $title = $name = $ico = '';
            $is_check = 1;
            $sort_num = $is_menu = 0;
        }

        $title = Elm::input('title', '权限名', $title)->required()->maxlength(45)->placeholder('请输入权限名称');
        $name = Elm::input('name', '权限', $name)->required()->maxlength(45)->placeholder('如：amdin/index/index');
        $ico = Elm::input('ico', '图标', $ico)->placeholder("请输入fa-xxx中的xxx部分");
        $sortNum = Elm::number('sort_num', '排序值', $sort_num);
        $is_check = Elm::radio('is_check', '是否验证', $is_check);
        $is_check->options(function(){
            $options = [['value'=>1, 'label'=>'需验证'], ['value'=>0, 'label'=>'无需验证']];
            return $options;
        });
        $is_menu = Elm::radio('is_menu', '是否菜单', $is_menu);
        $is_menu->options(function(){
            $radios = [['value'=>0, 'label'=>'非菜单'], ['value'=>1, 'label'=>'菜单']];
            return $radios;
        });
        $id = Elm::hidden('id', $id);
        $pid = Elm::hidden('pid', $pid);
        $form = Elm::createForm(Route::buildUrl('save'))->setMethod('POST');
        $form->setRule([$title, $name, $ico, $sortNum, $is_check, $is_menu, $id, $pid]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['pid', 0],
            ['title', ''],
            ['name', ''],
            ['ico', ''],
            ['sort_num', ''],
            ['is_check', 1],
            ['is_menu', 0],
        ]);
        $id = $data['id'];
        unset($data['id']);

        $data['name'] = strtolower($data['name']);

        if($id){
            $res = RuleModel::edit($data, $id, 'id');
        }else{
            $res = RuleModel::create($data);
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    public function delete($id=0)
    {
        $res = RuleModel::del($id);
        if($res){
            insert_log('删除权限', 'rule/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

}
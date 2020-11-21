<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 13:57
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use app\admin\model\system\Rule as RuleModel;
use sensen\services\UtilService;

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

    public function add()
    {
        return $this->fetch();
    }

    public function edit($id=0)
    {
        $info = RuleModel::get($id);
        $this->assign(['info'=>$info]);
        return $this->fetch();
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
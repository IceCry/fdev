<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 9:48
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\admin\model\system\Role as RoleModel;
use app\admin\model\system\Rule;
use sensen\services\JsonService;
use sensen\services\UtilService;

class Role extends AuthController
{
    /**
     * 首页视图
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取表格数据
     */
    public function getData()
    {
        return JsonService::successlayui(RoleModel::getRoleData());
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['title', '']
        ]);
        $id = $data['id'];
        unset($data['id']);
        if($id){
            $res = RoleModel::edit($data, $id, 'id');
        }else{
            $data['uuid'] = create_uuid();
            $res = RoleModel::create($data);
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    /**
     * 删除角色
     * @param int $id
     */
    public function delete($id=0)
    {
        $res = RoleModel::del($id);
        if($res){
            insert_log('删除角色', 'role/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 权限分配
     * @return string
     * @throws \Exception
     */
    public function rule()
    {
        return $this->fetch();
    }

    /**
     * 获取规则数据
     * @param int $id 当前角色id
     */
    public function getRuleData($id=0)
    {
        $rules = Rule::all(function ($query){
            $query->where(['is_check'=>1, 'status'=>1]);
        })->toArray();
        $info = RoleModel::get($id);
        $checked = explode(',', $info['rules']);
        foreach ($rules as $k=>$v){
            $rules[$k]['checked'] = 0;
            if(in_array($v['id'], $checked)){
                $rules[$k]['checked'] = 1;
            }
        }
        return JsonService::successlayui(0, $rules);
    }

    /**
     * 更新权限
     * @param int $id
     * @param int $rule_id
     * @param int $is_check
     */
    public function updateRule($id=0, $rule_id=0, $checked=0)
    {
        $info = RoleModel::get($id);
        $rules = explode(',', $info['rules']);
        if(!$rules[0]){
            $rules = [];
        }
        if($checked){
            array_push($rules, $rule_id);
        }else{
            $key = array_search($rule_id, $rules);
            if ($key !== false){
                array_splice($rules, $key, 1);
            }
        }
        $rules = array_unique($rules);
        $res = RoleModel::edit(['rules'=>implode(',', $rules)], $id, 'id');
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }



}
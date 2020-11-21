<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/27
 * Time: 10:39
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\admin\model\system\Admin as AdminModel;
use sensen\services\JsonService;
use sensen\services\UtilService;
use app\admin\model\system\Role;
use think\facade\Db;

class Admin extends AuthController
{

    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        $where = UtilService::getMore([
            ['keyword', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(AdminModel::getAdminData($where));
    }

    public function create()
    {
        $groups = Role::select();
        $this->assign(['groups'=>$groups]);
        return $this->fetch();
    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['account', ''],
            ['password', ''],
            ['name', ''],
            ['phone', ''],
            ['role_ids', []],
        ]);
        $id = $data['id'];
        unset($data['id']);

        //处理password
        if(isset($data['password']) && $data['password']){
            $data['salt'] = mt_rand(100000, 999999);
            $data['password'] = en_pwd($data['password'], $data['salt']);
        }else{
            unset($data['password']);
        }

        $roles = $data['role_ids'];
        $data['role_ids'] = implode(',', $data['role_ids']);
        $data['phone'] = $data['account'];

        if($id){
            Db::name('auth_group_access')->where('uid', $id)->delete();
            $res = AdminModel::edit($data, $id, 'id');
            $uid = $id;
        }else{
            $data['uuid'] = create_uuid();
            $res = AdminModel::create($data);
            $uid = $res->id;
        }
        if($res){
            foreach ($roles as $v){
                Db::name('auth_group_access')->insert(['uid'=>$uid, 'group_id'=>$v]);
            }
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    public function info($id=0)
    {
        $info = AdminModel::get($id);
        $info['password'] = '';
        return JsonService::success('ok', $info);
    }

    public function delete($id=0)
    {
        $res = AdminModel::del($id);
        if($res){
            insert_log('删除管理员', 'admin/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 密码修改
     * @return string
     * @throws \Exception
     */
    public function password()
    {
        return $this->fetch();
    }

    public function updatePassword()
    {
        $data = UtilService::postMore([
            ['old', ''],
            ['password', '']
        ]);
        if(!$data['old'] || !$data['password']){
            return JsonService::fail('请输入完整信息');
        }
        //检验旧密码
        $info = AdminModel::get($this->uid);
        if($info['password'] != en_pwd($data['old'], $info['salt'])){
            return JsonService::fail('旧密码输入错误');
        }
        $newData = [];
        $newData['salt'] = mt_rand(100000, 999999);
        $newData['password'] = en_pwd($data['password'], $newData['salt']);
        $res = AdminModel::where('id', $info['id'])->update($newData);
        if($res){
            insert_log('修改密码', 'admin/updatePassword', 1, '', $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }


}
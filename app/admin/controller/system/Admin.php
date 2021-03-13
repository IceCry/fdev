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
use FormBuilder\Factory\Elm;
use sensen\services\JsonService;
use sensen\services\UtilService;
use app\admin\model\system\Role;
use think\facade\Db;
use think\facade\Route;

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

    public function create($id=0)
    {
        $groups = Role::select();
        if($id>0){
            $info = AdminModel::get($id);
            $account = $info['account'];
            $name = $info['name'];
            $avatar = $info['avatar'];
            $phone = $info['phone'];
            $role_ids = intval($info['role_ids']);
            $status = intval($info['status']);
        }else{
            $account = $password = $name = $avatar = $phone = $role_ids = '';
            $status = 1;
        }
        $account = Elm::input('account', '帐号', $account)->required()->maxlength(45);
        if($id>0){
            $password = Elm::password('password', '密码')->minlength(6)->maxlength(20);
        }else{
            $password = Elm::password('password', '密码')->minlength(6)->maxlength(20)->required();
        }

        $name = Elm::input('name', '昵称', $name)->required()->maxlength(45);
        $phone = Elm::input('phone', '手机号', $phone)->maxlength(11);
        $avatar = Elm::uploadImage('icon', '头像', Route::buildUrl('widget.attach/upload'), $avatar);
        $role_ids = Elm::radio('role_ids', '角色', $role_ids)->required();
        $role_ids->options(function() use ($groups){
            $options = [];
            foreach ($groups as $v){
                $options[] = ['value'=>$v['id'], 'label'=>$v['title']];
            }
            return $options;
        });
        $status = Elm::radio('status', '是否启用', $status);
        $status->options(function(){
            $radios = [['value'=>1, 'label'=>'启用'], ['value'=>0, 'label'=>'禁用']];
            return $radios;
        });
        $id = Elm::hidden('id', $id);
        $form = Elm::createForm(Route::buildUrl('save'))->setMethod('POST');
        $form->setRule([$account, $password, $name, $phone, $avatar, $role_ids, $status, $id]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

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
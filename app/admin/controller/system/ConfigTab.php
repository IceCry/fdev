<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:48
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use sensen\services\JsonService;
use app\models\system\{
    Config as ConfigModel, ConfigTab as ConfigTabModel
};
use sensen\services\UtilService;

class ConfigTab extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        return JsonService::successlayui(ConfigTabModel::getConfigTabData());
    }

    public function add()
    {
        return $this->fetch();
    }

    public function edit($id=0)
    {
        $info = ConfigTabModel::get($id);
        $this->assign([
            'info'=>$info
        ]);
        return $this->fetch();
    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['pid', 0],
            ['title', ''],
            ['en_title', ''],
            ['info', ''],
            ['icon', '']
        ]);
        $id = $data['id'];
        unset($data['id']);

        if($id){
            $res = ConfigTabModel::edit($data, $id, 'id');
        }else{
            $res = ConfigTabModel::create($data);
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    public function delete($id=0)
    {
        //判断是否含配置项
        $hasConfig = ConfigModel::be(['config_tab_id'=>$id]);
        if($hasConfig){
            return JsonService::fail('存在配置项，无法删除！');
        }
        $res = ConfigTabModel::del($id);
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 配置字段
     * @return string
     * @throws \Exception
     */
    public function items()
    {
        return $this->fetch();
    }

    /**
     * 获取当前分类配置字段
     * @param int $id
     */
    public function getItemsData($id=0)
    {
        $data = ConfigModel::where('config_tab_id', $id)->select()->toArray();
        foreach ($data as &$v){
            $v['value'] = json_decode($v['value']);
        }
        $count = count($data);
        return JsonService::successlayui(compact('count', 'data'));
    }

    /**
     * 新增配置字段
     * @return string
     */
    public function addItem()
    {
        return $this->fetch();
    }

    public function editItem($id=0)
    {
        $info = ConfigModel::get($id);
        $info['value'] = json_decode($info['value']);
        $this->assign([
            'info'=>$info
        ]);
        return $this->fetch();
    }

    /**
     * 保存数据
     */
    public function saveItem()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['config_tab_id', 0],
            ['info', ''],
            ['menu_name', ''],
            ['desc', ''],
            ['type', 'text'],
            ['value', ''],
        ]);
        $id = $data['id'];
        unset($data['id']);

        $data['value'] = json_encode($data['value']);

        if($id>0){
            $res = ConfigModel::edit($data, $id, 'id');
        }else{
            //todo 暂定均为text
            //$data['type'] = 'text';
            $data['input_type'] = 'input';

            //判断是否已存在此字段
            $has = ConfigModel::where('menu_name', $data['menu_name'])->find();
            if($has){
                return JsonService::fail('当前变量已存在');
            }

            $res = ConfigModel::create($data);
        }
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }


    public function deleteConfig($id=0)
    {
        $res = ConfigModel::del($id);
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }


}
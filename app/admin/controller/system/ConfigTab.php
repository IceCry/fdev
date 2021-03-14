<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:48
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use FormBuilder\Factory\Elm;
use sensen\services\JsonService;
use app\models\system\{
    Config as ConfigModel, ConfigTab as ConfigTabModel
};
use sensen\services\UtilService;
use think\facade\Route;

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


    public function create($id=0)
    {
        if($id>0){
            $info = ConfigTabModel::get($id);
            $title = $info['title'];
            $enTitle = $info['en_title'];
            $intro = $info['info'];
            $status = $info['status'];
        }else{
            $title = $enTitle = $intro = '';
            $status = 1;
        }
        $title = Elm::input('title', '分类名称', $title)->required()->maxlength(45);
        $enTitle = Elm::input('en_title', '英文名称', $enTitle)->maxlength(45);
        $info = Elm::input('info', '描述', $intro)->maxlength(255);
        $status = Elm::radio('status', '状态', $status);
        $status->options(function(){
            $options = [['value'=>1, 'label'=>'显示'], ['value'=>0, 'label'=>'禁用']];
            return $options;
        });
        $id = Elm::hidden('id', $id);
        $form = Elm::createForm(Route::buildUrl('save'))->setMethod('POST');
        $form->setRule([$title, $enTitle, $info, $status, $id]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['pid', 0],
            ['title', ''],
            ['en_title', ''],
            ['info', ''],
            ['status', 1],
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
     * 新增配置项
     * @param int $id
     * @param int $config_tab_id
     * @return string
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createItem($id=0, $config_tab_id=0)
    {
        if($id>0){
            $info = ConfigModel::get($id);
            $name = $info['info'];
            $menuName = $info['menu_name'];
            $type = $info['type'];
            $desc = $info['desc'];
            $value = json_decode($info['value']);
        }else{
            $name = $menuName = $desc = $value = '';
            $type = 'text';
        }
        $name = Elm::input('info', '配置名称', $name)->required()->maxlength(45);
        $menuName = Elm::input('menu_name', '字段变量', $menuName)->required()->maxlength(45);
        $type = Elm::select('type', '数据类型', $type)->required();
        $type->options(function(){
            $options = [
                ['value'=>'text', 'label'=>'文本框'],
                ['value'=>'textarea', 'label'=>'多行文本框'],
                ['value'=>'radio', 'label'=>'单选'],
                ['value'=>'checkbox', 'label'=>'多选'],
                ['value'=>'select', 'label'=>'下拉'],
                ['value'=>'image', 'label'=>'图片'],
                ['value'=>'date', 'label'=>'日期'],
                ['value'=>'area', 'label'=>'地域'],
            ];
            return $options;
        });

        $desc = Elm::input('desc', '描述', $desc)->maxlength(255);
        $value = Elm::input('value', '默认值', $value);
        $id = Elm::hidden('id', $id);
        $configTabId = Elm::hidden('config_tab_id', $config_tab_id);
        $form = Elm::createForm(Route::buildUrl('saveItem'))->setMethod('POST');
        $form->setRule([$name, $menuName, $type, $desc, $value, $id, $configTabId]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
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
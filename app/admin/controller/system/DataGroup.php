<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 16:06
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use sensen\services\JsonService;
use app\admin\model\system\DataGroup as DataGroupModel;
use sensen\services\UtilService;

class DataGroup extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(DataGroupModel::getDataGroupData($where));
    }


    public function create()
    {
        return $this->fetch();
    }

    public function save()
    {
        $params = UtilService::postMore([
            ['id',''],
            ['name',''],
            ['config_name',''],
            ['info',''],
            ['fields',[]],
        ],$this->request);

        //数据组名称判断
        if(!$params['name'])return JsonService::fail('请输入数据组名称！');
        if(!$params['config_name'])return JsonService::fail('请输入数据字段！');
        //判断ID是否存在，存在就是编辑，不存在就是添加
        if(!$params['id']){
            if(DataGroupModel::be($params['config_name'], 'config_name')) return JsonService::fail('数据关键字已存在！');
        }
        $data["name"] = $params['name'];
        $data["config_name"] = $params['config_name'];
        $data["info"] = $params['info'];
        //字段信息判断
        if(!count($params['fields']))
            return JsonService::fail('字段至少存在一个！');
        else{
            $validate = ["name","type","var"];
            foreach ($params["fields"] as $key => $value) {
                if(!$value && in_array($key,$validate)) return JsonService::fail("字段不能为空！");
                $data["fields"][$key] = $value;
            }
        }
        $data["fields"] = htmlspecialchars_decode(json_encode($data["fields"]));
        //判断ID是否存在，存在就是编辑，不存在就是添加
        if(!$params['id']) {
            $res = DataGroupModel::create($data);
        }else{
            $res = DataGroupModel::edit($data, $params['id']);
        }
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 获取数据
     */
    public function info($id)
    {
        $dataGroup = DataGroupModel::get($id);
        $fields = json_decode($dataGroup['fields'],true);
        $typeList = [];
        foreach ($fields as $key => $v){
            $typeList[$key]['name'] = $v['name'];
            $typeList[$key]['var'] = $v['var'];
            $typeList[$key]['type'] = $v['type'];
            $typeList[$key]['param'] = $v['param'];
        }
        $dataGroup['fields'] = $typeList;
        return JsonService::successful('ok', $dataGroup);
    }

    /**
     * 删除
     * @param int $id
     * @return mixed
     */
    public function delete($id=0)
    {
        if(!DataGroupModel::del($id)){
            return JsonService::fail(DataGroupModel::getErrorInfo('删除失败！请重试'));
        }else{
            DataGroupValue::del(["gid"=>$id]);
            insert_log('删除组合数据', 'data_group/delete', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('删除成功!');
        }
    }


}
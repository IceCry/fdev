<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 16:27
 */

namespace app\admin\controller\system;


use app\admin\controller\AuthController;
use app\admin\model\system\DataGroup;
use app\admin\model\system\DataGroupValue as DataGroupValueModel;
use sensen\services\JsonService;
use sensen\services\UtilService;

class DataGroupValue extends AuthController
{
    /**
     * 获取指定数组数据
     * @param int $gid
     */
    public function index($gid=0)
    {
        $where = [];
        if ($gid) $where['gid'] = $gid;
        $this->assign('gid', $gid);
        $this->assign(DataGroup::getFields($gid));
        $this->assign(DataGroupValueModel::getList($where));
        return $this->fetch();
    }

    /**
     * 数据添加 暂定仅text
     * @param $gid
     * @return string
     * @throws \Exception
     */
    public function create($gid)
    {
        $fields = DataGroup::getFields($gid);
        $this->assign('fields', $fields['fields']);

        //处理不同类型数据
        /*$f = array();
        foreach ($fields["fields"] as $key => $value) {

        }*/

        return $this->fetch();
    }

    public function save()
    {
        $params = UtilService::postMore([
            ['id',''],
            ['gid',''],
            ['fields',[]],
        ],$this->request);

        //暂不做任何判断
        $fields = [];
        foreach ($params['fields'] as $k=>$v){
            $tmp = [];
            $tmp[$v['var']] = [
                'type'=>$v['type'],
                'value'=>$v['param']
            ];
            $fields[] = $tmp;
        }
        $data = [];
        $data['gid'] = $params['gid'];
        $data['value'] = htmlspecialchars_decode(json_encode($fields));
        $data['add_time'] = time();

        //判断ID是否存在，存在就是编辑，不存在就是添加
        if(!$params['id']) {
            $res = DataGroupValueModel::create($data);
        }else{
            $res = DataGroupValueModel::edit($data, $params['id']);
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
    public function info($gid, $id)
    {
        /*$dataGroup = DataGroup::get($id);
        $fields = json_decode($dataGroup['fields'],true);
        $typeList = [];
        foreach ($fields as $key => $v){
            $typeList[$key]['name'] = $v['name'];
            $typeList[$key]['var'] = $v['var'];
            $typeList[$key]['type'] = $v['type'];
            $typeList[$key]['param'] = $v['param'];
        }
        $dataGroup['fields'] = $typeList;*/
        $fields = DataGroup::getFields($gid);

        //追加是否启用选项
        /*$status = [
            'name'=>'是否启用',
            'var'=>'status',
            'type'=>'number',
            'param'=>'1'
        ];
        array_push($fields['fields'], $status);*/

        $newArr = [];
        if($id>0){
            $data = DataGroupValueModel::get($id);
            $values = json_decode($data['value'], true);
            foreach ($values as $k=>$v){
                foreach ($v as $x=>$z){
                    $newArr[$x] = $z;
                }
            }
        }

        foreach ($fields['fields'] as &$v){
            foreach ($newArr as $x=>$z){
                if($v['var'] == $x){
                    $v['param'] = $z['value'];
                }
            }
        }

        return JsonService::successful('ok', $fields['fields']);
    }

    /**
     * 删除
     * @param int $id
     * @return mixed
     */
    public function delete($id=0)
    {
        if(!DataGroupValueModel::del($id)){
            return JsonService::fail(DataGroupValueModel::getErrorInfo('删除失败！请重试'));
        }else{
            DataGroupValueModel::del(["gid"=>$id]);
            return JsonService::successful('删除成功！');
        }
    }

}
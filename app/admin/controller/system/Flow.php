<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/10
 * Time: 11:37
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\admin\model\system\FlowStep;
use FormBuilder\Factory\Elm;
use sensen\services\JsonService;
use app\admin\model\system\Flow as FlowModel;
use sensen\services\UtilService;
use think\facade\Db;
use think\facade\Route;

class Flow extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function getData()
    {
        return JsonService::successlayui(FlowModel::getFlowData());
    }

    public function create($id=0)
    {
        if($id>0){
            $info = FlowModel::get($id);
            $name = $info['name'];
            $type = $info['type'];
            $intro = $info['intro'];
            $number = $info['prefix_num'];
            $restartMode = intval($info['restart_mode']);
            $status = intval($info['status']);
        }else{
            $name = $type = $intro = $number = '';
            $restartMode = 0;
            $status = 1;
        }
        $name = Elm::input('name', '流程名称', $name)->required()->maxlength(45);
        $type = Elm::input('type', '唯一标识', $type)->required()->maxlength(15);
        $intro = Elm::input('intro', '流程简介', $intro);
        $number = Elm::input('prefix_num', '编号规则', $number);
        $restartMode = Elm::radio('restart_mode', '修改模式', $restartMode);
        $restartMode->options(function(){
            $options = [['value'=>0, 'label'=>'重走流程'], ['value'=>1, 'label'=>'继续向下']];
            return $options;
        });
        $status = Elm::radio('status', '状态', $status);
        $status->options(function(){
            $options = [['value'=>1, 'label'=>'显示'], ['value'=>0, 'label'=>'禁用']];
            return $options;
        });
        $id = Elm::hidden('id', $id);
        $form = Elm::createForm(Route::buildUrl('save'))->setMethod('POST');
        $form->setRule([$name, $type, $intro, $number, $restartMode, $status, $id]);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }

    public function save()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['type', ''],
            ['name', ''],
            ['intro', ''],
            ['prefix_num', ''],
            ['restart_mode', 0],
        ]);
        $id = $data['id'];
        unset($data['id']);

        if($id){
            $res = FlowModel::edit($data, $id, 'id');
        }else{
            $data['user_id'] = $this->userInfo['id'];
            $data['uuid'] = create_uuid();
            $res = FlowModel::create($data);
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    public function delete($id=0)
    {
        $res = FlowModel::del($id);
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }


    /**
     * 流程对应步骤
     */
    public function step($flow_id=0)
    {

        //获取当前步骤数据
        /*
        {
            "total": 4, //数量
            "list": [
                "id": 1,
                "mode": "指定角色", //审核人类型
                "name": "发起案件",
                "step_name": "流程名",
                "next_step_id": "1,2", //下一步
                "style":"",
            ]
        }
        */

        $steps = FlowStep::where(['flow_id'=>$flow_id, 'delete_time'=>0])->select();
        $total = count($steps);

        $list = [];
        $firstId = $lastId = 0;
        foreach ($steps as $k=>$v){
            if($k==0){
                $firstId = $v['id'];
            }elseif($k+1 == $total){
                $lastId = $v['id'];
            }
            $style = json_decode($v['style'], true);
            $list[$k]['id'] = $v['id'];
            $list[$k]['checker_type'] = get_checker_type($v['checker_type']);
            $list[$k]['checker_name'] = $v['checker_name'];
            $list[$k]['step_name'] = $v['name'];
            $list[$k]['next_step_id'] = $v['next_step_id'];

            $style['height'] = $style['height']=='auto'?'auto':$style['height'].'px';

            $list[$k]['style'] = 'width:' . $style['width'] . 'px;height:' . $style['height'] . ';line-height:30px;color:'.$style['color'].';background:'.$style['background'].';left:' . $v['margin_left'] . 'px;top:' . $v['margin_top'] . 'px;border-radius:5px;';

            //处理condition
            $condition_str = '';
            if($v['out_condition']){
                foreach (explode(',', $v['next_step_id']) as $v2){
                    foreach ($v['out_condition'] as $k3=>$v3){
                        if($v2==$k3){
                            $condition_str .= $v3->title.',';
                        }
                    }
                }
            }
            $condition_str = trim($condition_str, ',');
            $list[$k]['condition_str'] = $condition_str;
        }

        //默认追加开始+结束
        /*$start = [
            'id'=>0,
            'checker_type'=>'',
            'checker_name'=>'',
            'step_name'=>'开始',
            'next_step_id'=>$firstId,
            'style'=>'width:60px;height:auto;line-height:30px;color:#ffffff;background:#ffb952;left:20px;top:20px;border-radius:5px;'
        ];
        array_unshift($list, $start);
        $end = [
            'id'=>0,
            'checker_type'=>'',
            'checker_name'=>'',
            'step_name'=>'结束',
            'next_step_id'=>'',
            'style'=>'width:60px;height:auto;line-height:30px;color:#ffffff;background:#ffb952;left:20px;top:500px;border-radius:5px;'
        ];
        array_push($list, $end);*/

        $stepData['total'] = $total;
        $stepData['list'] = $list;
        $stepData = json_encode($stepData);

        $this->assign('stepData', $stepData);

        return $this->fetch();
    }

    public function stepCreate()
    {
        //审核人员类型
        $checker = $this->getDataValue('flow_checker');
        //获取部门及id
        $dept = [];

        //获取角色及id
        $role = Db::name('auth_group')->where(['delete_time'=>0, 'status'=>1])->field('id, title')->select();

        $this->assign(['checker'=>$checker, 'role'=>$role]);
        return $this->fetch();
    }

    public function stepSave()
    {
        $data = UtilService::postMore([
            ['id', 0],
            ['flow_id', 0],
            ['name', ''],
            ['intro', ''],
            ['for_user_type', ''],
            ['for_user_name', ''],
            ['for_user_id', ''],
            ['checker_type', ''],
            ['checker_name', ''],
            ['checker_id', ''],
            ['can_transfer', 1],
            ['transfer_type', ''],
            ['transfer_name', ''],
            ['transfer_id', ''],
            ['out_condition', []],
            ['can_back', 1],
            ['mode', 0],
            ['actions', ''],
            ['is_sms', 1],
            ['is_wechat', 1],
            ['pre_step_id', ''],
            ['next_step_id', ''],
        ]);
        $id = $data['id'];
        unset($data['id']);

        $data['out_condition'] = $data['out_condition']?json_encode($data['out_condition']):'';

        //暂定后台处理相关name值
        $data['for_user_name'] = $this->getFlowTypeName($data['for_user_type'], $data['for_user_id']);
        $data['checker_name'] = $this->getFlowTypeName($data['checker_type'], $data['checker_id']);
        $data['transfer_name'] = $this->getFlowTypeName($data['transfer_type'], $data['transfer_id']);

        //pre_step_id next_step_id 可同时存在多个

        //判断当前流程包含步骤数
        if(!$id){
            $lastStep = FlowStep::where(['flow_id'=>$data['flow_id'], 'delete_time'=>0])->order('id desc')->find();
            if($lastStep){
                //在最后一个下方创建
                $left = $lastStep['margin_left'] + 60;
                $top = $lastStep['margin_top'] + 60;
                $isFirst = 0;
            }else{
                $left = 100;
                $top = 100;
                $isFirst = 1;
            }

            $data['margin_left'] = $left;
            $data['margin_top'] = $top;
            $data['is_first'] = $isFirst;
            $data['style'] = json_encode([
                'width'=>'200',
                'height'=>'auto',
                'color'=>'#ffffff',
                'background'=>'#68b8f7'
            ]);
        }

        if($id){
            unset($data['flow_id']);
            $res = FlowStep::edit($data, $id, 'id');
        }else{
            $data['user_id'] = $this->userInfo['id'];
            $data['uuid'] = create_uuid();
            $res = FlowStep::create($data);
            /*if($isFirst){
                //如果无第一步则需自动创建开始
                $firstData = [
                    'flow_id'=>$data['flow_id'],
                    'name'=>'开始',
                    'intro'=>'启动流程',
                    'next_step_id'=>$res->id,
                    'user_id'=>$this->userInfo['id'],
                    'uuid'=>create_uuid(),
                    'margin_left'=>20,
                    'margin_top'=>20,
                    'style'=>json_encode([
                        'width'=>'100',
                        'height'=>'auto',
                        'color'=>'#ffffff',
                        'background'=>'#ffb952'
                    ])
                ];
                FlowStep::create($firstData);
            }*/
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }


    /**
     * 保存连线关系
     * @param int $flow_id
     * @param string $link_info 连线信息
     */
    public function saveCanvas($flow_id=0, $link_info='')
    {
        $flow = FlowModel::get($flow_id);
        if(!$flow){
            return JsonService::fail('未找到流程数据');
        }
        $linkInfo = json_decode(htmlspecialchars_decode(trim($link_info)), true);

        $res = false;
        foreach ($linkInfo as $step_id => $v) {
            //判断流程模式 暂仅支持单线及条件转出
            if(count($v['next_step_id'])>1){
                $mode = 1;
            }else{
                $mode = 0;
            }

            $res = FlowStep::edit([
                'margin_left'=>(int)$v['left'],
                'margin_top'=>(int)$v['top'],
                'next_step_id'=>self::parseNextStepId($v['next_step_id']),
                'mode'=>$mode
            ], $step_id, 'id');
        }
        if($res){
            return JsonService::successful('保存成功');
        }else{
            return JsonService::fail('保存失败！请重试');
        }
    }

    public function stepInfo($id=0)
    {
        $info = FlowStep::find($id)->toArray();

        //判断类型 单线or转出
        $nextArr = explode(',', $info['next_step_id']);
        $stepCount = count($nextArr);
        if($stepCount>1){
            $info['is_single'] = 0;
            //条件转出对应下级步骤名称
            $info['next_step'] = FlowStep::where('id', 'in', $info['next_step_id'])->field('id, name')->select();
            //获取当前 out_condition 条件数
            if(!$info['out_condition']){
                $condition = [];
                foreach ($nextArr as $v){
                    $condition[$v]['title'] = "";
                    $condition[$v]['condition'] = "";
                }
                $info['out_condition'] = $condition;
            }

        }else{
            $info['is_single'] = 1;
            $info['next_step'] = [];
        }

        return JsonService::success('ok', $info);
    }

    public function stepDelete($id=0)
    {
        $res = FlowStep::del($id);

        //todo 处理上下级关系

        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 拼装next_step_id
     * @param $arr
     * @return string
     */
    private static function parseNextStepId($arr)
    {
        if (!$arr) return '';

        $arr = is_array($arr)?$arr:explode(',', $arr);
        $arr = array_unique($arr);

        $ids = [];
        foreach ($arr as $id) {
            $id = intval($id);
            if ($id > 0) {
                array_push($ids, $id);
            }
        }
        return implode(',', $ids);
    }

    /**
     * 根据对应类型获取审核人
     * @param string $type
     * @param string $keyword
     */
    public function searchUserTypeData($type='', $keyword='')
    {
        $data = [];
        if($type=='user'){
            //获取所有部门 分类展示用户
            $depts = [];
            foreach ($depts as $k=>$v){
                $tmpArr = [];
                $tmpUser = Db::name('user')->where(['dept_id'=>$v['id'],'delete_time'=>0, 'status'=>1])->field('id as value, name')->select()->toArray();
                if(count($tmpUser)==0){
                    continue;
                }
                $tmpArr['name'] = $v['name'];
                $tmpArr['children'] = $tmpUser;
                $data[] = $tmpArr;
            }
        }else if($type=='dept'){
            $data = Db::name('lawfirm_dept')->where(['type'=>1, 'delete_time'=>0, 'status'=>1])->field('id as value, name')->select();
        }else if($type == 'role'){
            $data = Db::name('auth_group')->where(['delete_time'=>0, 'status'=>1])->field('id as value, title as name')->select();
        }
        return JsonService::successlayui(0, $data);
    }




}
<?php
/**
 * Desc: 流程
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/14
 * Time: 15:26
 */

namespace app\services;

use app\index\model\system\Flow;
use app\index\model\system\FlowBill;
use app\index\model\system\FlowProcess;
use app\index\model\system\FlowStep;
use app\index\model\system\Todo;
use app\models\lawfirm\User;
use sensen\jobs\SmsJob;
use sensen\jobs\WechatTemplateJob;
use sensen\services\GatewayClientService;
use sensen\utils\Queue;
use think\facade\Db;

class WorkFlow extends BaseService
{
    //todo 只能获取当前 lawfirm_id

    //前置操作
    protected $beforeActionList = [
        'isFlow' => ['only'=>'save'],
    ];


    //创建审批流程
    public static function save($type='', $where="", $addUser=[], $data=[])
    {
        //获取流程
        $flow = self::getFlowByType($type, $addUser['lawfirm_id']);
        //获取下一步
        $next = self::getNextStep($flow['id'], 0, $where, $addUser);
        //获取通知人
        $userArr = self::getStepCheckUser($next, $addUser);

        //创建bill
        $number = self::createNumber($flow);
        $join_uids = self::getAllCheckUid($flow['id'], $addUser);

        Flow::beginTrans();
        $res1 = FlowBill::insertBill($number, $flow, $userArr['status_text'], $next['id'], $userArr['user_id'], $userArr['user_name'], $addUser, $data, $join_uids['uids']);
        //写入process
        $res2 = FlowProcess::insertProcess($flow['id'], $res1->id, $next['id'], 0, $userArr['status_text'], 0, $userArr['user_id'], $userArr['user_name'], $data['attach_ids'], $data['remark'], 0, 0);
        $res = $res1 && $res2;
        Flow::checkTrans($res);

        //插入待办工作
        Todo::insertTodo($userArr['user_id'], $data['title'], $data['message'], $data['mtable'], $data['mid'], $data['muuid'], 1, $data['url']);

        //通知
        $noticeData = [];
        self::sendNotice($userArr['user_id'], $noticeData, $next['is_sms'], $next['is_wechat'], $addUser['lawfirm_id']);

        return $res;
    }

    //生成编号
    public static function createNumber($flow=[])
    {
        //编号生成规则 默认：类型+201216+001
        $count = FlowBill::where('flow_id', $flow['id'])->whereDay('create_time')->count();
        $count+=1;
        return $flow['prefix_num'] . date('ymd') . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    //检测业务流程是否合法
    public static function isValid($type='')
    {
        return true;
        //是否设置流程

        //流程是否正确

        //服务是否到期

    }


    //获取流程
    public static function getFlowByType($type='', $lawfirm_id=0)
    {
        //todo lawfirm_id
        return Flow::get(['type'=>$type]);
    }

    //获取下一步
    public static function getNextStep($flow_id=0, $now_step_id=0, $where="", $addUser=[])
    {
        //todo 判断提交人是否在审批流程中，需获取流程各类型对应的人员进行判断，决定起始步骤；且为最高步骤；仅第一步判断

        //todo nextStep可能为多个
        //判断是否为第一步
        if($now_step_id>0){
            $now = FlowStep::where(['flow_id'=>$flow_id, 'id'=>$now_step_id])->find();
            //如果下一步为多个步骤
            if($now['mode'] == 1){
                //根据条件筛选出对应的下一步
                //优先判断是否存在匹配，不存在则默认无条件
                $nextId = $defaultId = 0;
                foreach ($now['out_condition'] as $k=>$v){
                    if($v['condition'] == $where){
                        $nextId = $k;
                    }
                    if(!$v['condition']){
                        $defaultId = $k;
                    }
                }
                if(!$nextId){
                    $nextId = $defaultId;
                }
                $next = FlowStep::where('id', $nextId)->find();
            }else{
                $next = FlowStep::where('id', 'in', $now['next_step_id'])->find();
            }
        }else{
            //判断申请人是否为审核人
            //获取整个流程中审核人，由高到低
            //开始即按条件流转情况
            $next = self::checkUserInStep($flow_id, $addUser);
            if(!$next){
                $next = FlowStep::where(['flow_id'=>$flow_id, 'is_first'=>1])->find();
            }
            //判断是否为条件转办
            if($next['mode']==1){
                $nextId = $defaultId = 0;
                foreach ($next['out_condition'] as $k=>$v){
                    if($v['condition'] == $where){
                        $nextId = $k;
                        break;
                    }else{
                        if(!$v['condition']){
                            $defaultId = $k;
                        }
                    }
                }
                if(!$nextId){
                    $nextId = $defaultId;
                }
                $next = FlowStep::where('id', $nextId)->find();
            }
        }
        $next = $next ? $next : [];
        return $next;
    }

    //获取指定步骤审核人
    public static function getStepCheckUser($step=[], $addUser=[])
    {
        $userArr = [
            'user_id' => '',
            'user_name' => '',
            'status_text' => '等待<strong style="color:blue;">'.$step['checker_name'].'</strong>审核',
            'now_step_id' => $step['id'],
            'is_in'=>0, //当前用户是否在本步骤
        ];

        if($step['checker_type'] == 'user'){
            $userArr['user_id'] = $step['checker_id'];
            $userArr['user_name'] = $step['checker_name'];
        }elseif($step['checker_type'] == 'dept'){
            //指定部门
            $users = Db::name('user')->where(['delete_time'=>0, 'status'=>1, 'lawfirm_id'=>$addUser['lawfirm_id']])->where('dept_id', 'in', $step['checker_id'])->select();
            foreach ($users as $v){
                $userArr['user_id'] .= $v['id'] . ',';
                $userArr['user_name'] .= $v['name'] . ',';
            }
            $userArr['user_id'] = trim($userArr['user_id'], ',');
            $userArr['user_name'] = trim($userArr['user_name'], ',');
        }elseif($step['checker_type'] == 'super'){
            //上级领导，需检验本部门是否设置负责人  todo 第一步为super可能导致错误，dept_id未知
            $leader = Db::name('lawfirm_dept')->where('id', $addUser['dept_id'])->field('id, leader_id, leader_name')->find();
            $userArr['user_id'] = $leader['leader_id'];
            $userArr['user_name'] = $leader['leader_name'];
        }elseif($step['checker_type'] == 'group'){
            //职位 一个用户可能包含多种角色
            //like 2,3 in 2,4,5
            //way1 获取所有用户，遍历处理
            $allUser = Db::name('user')->where(['lawfirm_id'=>$addUser['lawfirm_id'], 'delete_time'=>0, 'status'=>1])->select();
            $checkUser = explode(',', $step['checker_id']);
            foreach ($allUser as $k=>$v){
                $groups = explode(',', $v['group_id']);
                if(count($groups)>1){
                    foreach ($groups as $z){
                        if(in_array($z, $checkUser)){
                            $userArr['user_id'] .= $v['id'].',';
                            $userArr['user_name'] .= $v['realname'].',';
                        }
                    }
                }else{
                    if(in_array($v['group_id'], $checkUser)){
                        $userArr['user_id'] .= $v['id'].',';
                        $userArr['user_name'] .= $v['realname'].',';
                    }
                }
            }
            $userArr['user_id'] = trim($userArr['user_id'], ',');
            $userArr['user_name'] = trim($userArr['user_name'], ',');
        }elseif($step['checker_type'] == ''){
            //未找到待办人，流程结束
            $userArr['user_id'] = '0';
            $userArr['user_name'] = '完结';
        }
        if(in_array($addUser['id'], explode(',', $userArr['user_id']))){
            $userArr['is_in'] = 1;
        }
        return $userArr;
    }

    //检测用户是否在审批流程中
    public static function checkUserInStep($flow_id=0, $addUser=[])
    {
        //todo 条件转出情况如何确定优先级；如均存在此用户
        //todo 需依据当前条件判断进入哪个分支
        $all = self::getAllCheckUid($flow_id, $addUser);
        //获取所在最高审核级别
        //todo 当前依据ID最高则为最高，设计流程时需依据此规范
        if(count($all['steps'])>0){
            $max = FlowStep::where('id', 'in', $all['steps'])->order('id desc')->find();
        }else{
            $max = [];
        }
        return $max;
    }

    //获取指定流程所有参与审查人员
    public static function getAllCheckUid($flow_id=0, $addUser=[])
    {
        $steps = FlowStep::where(['flow_id'=>$flow_id, 'delete_time'=>0, 'status'=>1])->select();
        $inStep = []; //用户所在步骤
        $uid = '';
        foreach ($steps as $v){
            $arr = self::getStepCheckUser($v, $addUser);
            $uid .= $arr['user_id'].',';
            if($arr['is_in']==1){
                $inStep[] = $v['id'];
            }
        }
        //去重
        $uid = trim($uid, ',');
        $uidArr = array_unique(explode(',', $uid));
        sort($uidArr); //排序 否则下步bug
        $key=array_search('0', $uidArr);
        array_splice($uidArr, $key, 1);
        $uids = implode(',', $uidArr);
        return ['uids'=>$uids, 'steps'=>$inStep];
    }

    /**
     * 步骤审核
     * @param int $bill_id 单据id
     * @param int $result 审核结果 1通过 2转办 -1拒绝 -2退回
     * @param int $process_id 当前process
     * @param int $back_process_id 退回到指定步骤
     * @param string $attach_ids 附件
     * @param array $userArr 当前用户
     * @param string $remark 备注
     * @param int $transfer_uid 转办人id
     * @return FlowProcess|\think\Model
     * @throws \think\db\exception\DbException
     */
    public static function doCheck($bill_id=0, $result=0, $process_id=0, $back_process_id=0, $attach_ids='', $userArr=[], $remark='', $transfer_uid=0)
    {
        /**
         * 审核流程
         * 1. 更新主表状态（仅完成后更新）
         * 2. 更新bill
         * 3. 新增process
         * 4. 发送通知（向下或向上）
         *
         *
         * 新业务，写入bill，now_step_id记录待审核人信息；
         *
         */
        $is_back = $is_transfer = 0;
        $bill = FlowBill::get(['id'=>$bill_id]);
        $step = FlowStep::get($bill['now_step_id']);

        //检测是否有权限审核
        /*if(!self::canCheck($step, $userArr)){
            return ['code'=>403, 'msg'=>'非法操作'];
        }*/
        $nextStepId = 0;
        if($result==1){
            //通过则通知下一位或结束
            $addUser = User::get($bill['user_id']);
            $next = self::getNextStep($bill['flow_id'], $bill['now_step_id']);

            if($next){
                $nextUser = self::getStepCheckUser($next, $addUser);
                $uids = $nextUser['user_id'];
                $nextStepId = $next['id'];
                $nowStatusText = "等待<strong style='color:blue;'>".$step['checker_name']."</blue>审核";
            }else{
                $uids = '0';
                //标记为已完成
                Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                    'flow_status'=>config('code.FLOW_DONE'),
                    'update_time'=>time()
                ]);
                $nowStatusText = "<span style='color:green;'>审核已通过</span>";
            }
        }elseif($result==2){
            //todo 转办 暂不考虑
            $is_transfer = $bill['now_step_id'];
            //获取办理人
            $uids = $transfer_uid;
            $nowStatusText = "转发给xx办理";
        }elseif($result==-1){
            //拒绝则通知申请人
            $uids = $bill['user_id'];
            //标记为拒绝
            Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                'flow_status'=>config('code.FLOW_REJECT'),
                'update_time'=>time()
            ]);
            $nowStatusText = "<span style='color:red;'>申请已拒绝</span>";
        }elseif($result==-2){
            //退回到指定人
            $process = FlowProcess::get($back_process_id);
            $uids = $process['user_id'];
            $is_back = 1;
            $nextStepId = $process['now_step_id']; //todo 获取 next

            //回滚状态
            $nowStatusText = "退回到".$process['user_name']."处理";
        }

        //写入process表
        $res = FlowProcess::insertProcess($bill['flow_id'], $bill['id'], $nextStepId, $bill['now_step_id'], $nowStatusText, $result, $userArr['id'], $userArr['name'], $attach_ids, $remark, $is_back, $is_transfer);
        //todo 标记当前process为已处理
        FlowProcess::where('id', $process_id)->update([
            'deal_time'=>time(),
            'result_status'=>$result
        ]);

        //通知
        $noticeData = [];
        self::sendNotice($uids, $noticeData, $step['is_sms'], $step['is_wechat'], $bill['lawfirm_id']);

        if($res){
            return ['code'=>200, 'msg'=>'操作成功'];
        }else{
            return ['code'=>400, 'msg'=>'操作失败！请重试'];
        }
    }

    /**
     * 判断用户当前步骤是否有审核权限
     * @param array $step
     * @param array $userArr
     * @return bool
     */
    public static function canCheck($step=[], $userArr=[])
    {
        //检测是否有权限审核
        $canCheckUser = self::getStepCheckUser($step, $userArr);
        $canCheckUserIds = explode(',', $canCheckUser['user_id']);
        if(!in_array($userArr['id'], $canCheckUserIds)){
            return 0;
        }
        return 1;
    }

    //发送通知
    //包含审核人及申请人
    //$data= ['sms'=>[], 'wechat'=>[]];
    public static function sendNotice($uids='', $data=[], $isSms=0, $isWechat=0, $topId=0)
    {
        //todo 待开启
        return true;

        if(!$uids){
            return true;
        }
        //通知 依据是否通知 写入队列
        $noticeArr = User::where('id', 'in', $uids)->field('id,phone,name,openid')->select();
        if($isSms){
            foreach ($noticeArr as $v){
                if(!$v['phone']) continue;
                $smsData = [];
                Queue::instance()->do('doJob')->job(SmsJob::class)->data(true, $v['phone'], '', $smsData, $topId)->push();
            }
        }
        if($isWechat){
            foreach ($noticeArr as $v){
                if(!$v['openid']) continue;
                $tplData = [];
                Queue::instance()->do('sendFlowSuccess')->job(WechatTemplateJob::class)->data($v['openid'], $tplData, $topId)->push();
            }
        }
        //WebSocket推送
        $uidsArr = explode(',', $uids);
        GatewayClientService::sendToUid($uidsArr, ['type'=>'notice', 'data'=>'test']);
    }




}
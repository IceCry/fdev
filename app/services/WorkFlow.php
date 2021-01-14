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
use app\models\finance\Finance;
use app\models\lawfirm\User;
use sensen\jobs\SmsJob;
use sensen\jobs\WechatTemplateJob;
use sensen\services\GatewayClientService;
use sensen\utils\Queue;
use think\facade\Db;

class WorkFlow extends BaseService
{
    //前置操作
    protected $beforeActionList = [
        'isFlow' => ['only'=>'save'],
    ];

    /**
     * 创建审批流程
     * @param string $type 业务类型
     * @param array $addUser 操作者
     * @param array $data
     * @param string $mtable 中间表
     * @param int $mid 中间表id
     * @return bool
     */
    public static function save($type='', $addUser=[], $data=[], $mid=0)
    {
        //获取流程
        $flow = self::getFlowByType($type);
        //获取下一步
        $next = self::getNextStep($flow['id'], 0, $addUser, $flow['mtable'], $mid);
        //获取通知人
        $userArr = self::getStepCheckUser($next, $addUser);

        //创建bill
        $number = self::createNumber($flow);
        $join_uids = self::getAllCheckUid($flow['id'], $addUser);

        Flow::beginTrans();
        //写入bill
        $res1 = FlowBill::insertBill($number, $flow, $userArr['status_text'], $next['id'], $userArr['user_id'], $userArr['user_name'], $addUser, $data, $join_uids['uids']);
        //写入process
        $res2 = FlowProcess::insertProcess($flow['id'], $res1->id, $next['id'], 0, $userArr['status_text'], 0, $userArr['user_id'], $userArr['user_name'], $data['attach_ids'], $data['remark'], 0, 0);
        //写入todo
        $res3 = Todo::insertTodo($userArr['user_id'], $data['title'], $data['message'], $data['mtable'], $data['mid'], $data['muuid'], 1, $data['url'], $res1->id, $addUser);
        $res = $res1 && $res2 && $res3;
        Flow::checkTrans($res);

        //通知
        $noticeData = [];
        self::sendNotice($userArr['user_id'], $noticeData, $next['is_sms'], $next['is_wechat']);
        return $res;
    }

    /**
     * 生成编号
     * @param array $flow 流程信息
     * @return string
     */
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


    /**
     * 根据类型获取流程
     * @param string $type 类型
     * @return mixed
     */
    public static function getFlowByType($type='')
    {
        return Flow::get(['type'=>$type]);
    }

    /**
     * 获取下一步
     * @param int $flow_id 流程id
     * @param int $now_step_id 当前步骤id
     * @param array $addUser 当前用户
     * @param string $mtable 中间表
     * @param int $mid 中间表id
     * @return array|null|\think\Model
     */
    public static function getNextStep($flow_id=0, $now_step_id=0, $addUser=[], $mtable='', $mid=0)
    {
        //todo 判断提交人是否在审批流程中，需获取流程各类型对应的人员进行判断，决定起始步骤；且为最高步骤；仅第一步判断
        //todo 不考虑提交人非承办律师
        //判断是否为第一步
        if($now_step_id>0){
            $now = FlowStep::where(['flow_id'=>$flow_id, 'id'=>$now_step_id])->find();
            //如果下一步为多个步骤
            if($now['mode'] == 1){
                //根据条件筛选出对应的下一步，优先判断是否存在匹配，不存在则默认无条件
                $nextId = $defaultId = 0;
                foreach ($now['out_condition'] as $k=>$v){
                    //按out_condition查询判断是否存在此条记录
                    if($v['condition']){
                        $canFind = Db::name($mtable)->where($v['condition'])->where('id', $mid)->find();

                        if($canFind){
                            $nextId = $k;
                            //禁止break跳出，避免默认无条件情况
                        }
                    }else{
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
            //todo 暂定所有流程需从0开始，避免多分支造成，如：部门主任提交需经所主任审批类型案件产生的问题
            //$next = self::checkUserInStep($flow_id, $addUser);
            $next = [];
            if(!$next){
                $next = FlowStep::where(['flow_id'=>$flow_id, 'is_first'=>1])->find();
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
            $users = Db::name('user')->where(['delete_time'=>0, 'status'=>1])->where('dept_id', 'in', $step['checker_id'])->select();
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
        }elseif($step['checker_type'] == 'dept_leader'){
            //上级领导，需检验本部门是否设置负责人
            $leader = Db::name('user')->where('dept_id', $addUser['dept_id'])->whereFindInSet('roles', config('code.LEADER_ROLE'))->field('id, name, roles')->find();
            $userArr['user_id'] = $leader['id'];
            $userArr['user_name'] = $leader['name'];
        }elseif($step['checker_type'] == 'role'){
            //职位 一个用户可能包含多种角色
            //like 2,3 in 2,4,5
            //way1 获取所有用户，遍历处理
            $allUser = Db::name('user')->where(['delete_time'=>0, 'status'=>1])->select();
            $checkUser = explode(',', $step['checker_id']);
            foreach ($allUser as $k=>$v){
                $groups = explode(',', $v['roles']);
                if(count($groups)>1){
                    foreach ($groups as $z){
                        if(in_array($z, $checkUser)){
                            $userArr['user_id'] .= $v['id'].',';
                            $userArr['user_name'] .= $v['name'].',';
                        }
                    }
                }else{
                    if(in_array($v['roles'], $checkUser)){
                        $userArr['user_id'] .= $v['id'].',';
                        $userArr['user_name'] .= $v['name'].',';
                    }
                }
            }
            $userArr['user_id'] = trim($userArr['user_id'], ',');
            $userArr['user_name'] = trim($userArr['user_name'], ',');
        }elseif($step['checker_type']=='proposer'){
            $userArr['user_id'] = $addUser['id'];
            $userArr['user_name'] = $addUser['name'];
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

    /**
     * 获取指定流程所有参与审查人员
     * @param int $flow_id 流程id
     * @param array $addUser 当前用户
     * @return array
     */
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
     * @param array $userArr 当前用户
     * @param int $transfer_uid 转办人id
     * @param int $transfer_uname 转办人
     * @param array $data 备注remark 申请事项title 附件attach_ids 跳转地址url flow_id流程id
     * @return FlowProcess|\think\Model
     * @throws \think\db\exception\DbException
     */
    public static function doCheck($bill_id=0, $result=0, $process_id=0, $back_process_id=0, $userArr=[], $data=[], $transfer_uid=0, $transfer_uname='')
    {
        /**
         * 审核流程
         * 1. 更新主表状态（仅完成后更新）
         * 2. 更新bill
         * 3. 新增process
         * 4. 发送通知（向下或向上）
         *
         * 新业务，写入bill，now_step_id记录待审核人信息；
         * todo 使用事务
         */
        $is_back = $is_transfer = 0;
        $bill = FlowBill::get(['id'=>$bill_id]);
        //获取当前所在步骤，最新步骤
        $lastProcess = FlowProcess::getLastProcess($bill_id);
        $step = FlowStep::get($lastProcess['now_step_id']);

        //todo 检测是否有权限审核
        /*if(!self::canCheck($step, $userArr)){
            return ['code'=>403, 'msg'=>'非法操作'];
        }*/

        //获取下一步审核人
        $nextUser = [
            'user_id'=>'',
            'user_name'=>''
        ];

        //下一步id
        //todo 同一个表不同流程，需区分对应处理
        $flowType = Flow::where('id', $bill['flow_id'])->value('type');

        $nextStepId = 0;
        $doneTime = 0; //审批完成时间
        if($result==1){
            //通过则通知下一位或结束
            $addUser = User::get($bill['user_id']);
            $next = self::getNextStep($bill['flow_id'], $lastProcess['now_step_id'], $userArr, $bill['mtable'], $bill['mid']);
            if($next){
                $nextUser = self::getStepCheckUser($next, $addUser);
                $uids = $nextUser['user_id'];
                $nextStepId = $next['id'];
                $nowStatusText = "等待<strong style='color:blue;'>".$next['checker_name']."</blue>审核";
            }else{
                $doneTime = time();
                $uids = '0';
                //标记为已完成
                //todo 一个表对应多种流程，如案件：新案件审核、临时金额、结案等

                if($bill['mtable']=='case_info'){
                    if($flowType=='case_start'){
                        //新业务
                        Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                            'flow_status'=>config('code.FLOW_DONE'),
                            'update_time'=>time()
                        ]);
                    }elseif($flowType=='case_end'){
                        //结案

                    }elseif($flowType=='case_tmp_money'){
                        //临时应收金额
                        //更新案件主表实际收费金额、应收金额、临时金额状态
                        Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                            'need'=>$lastProcess['remark'],
                            'received'=>Db::raw("received+{$data['amount']}"),
                            'tmp_money_status'=>config('code.TMP_MONEY_SUCCESS'),
                            'update_time'=>time()
                        ]);
                        //写入收费记录表
                        Finance::insertLog($data['amount'], $data['payby'], $data['remark'], $userArr['id'], $userArr['name'], 1, $bill['mid']);
                    }
                }else{
                    Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                        'flow_status'=>config('code.FLOW_DONE'),
                        'update_time'=>time()
                    ]);
                }

                $nowStatusText = "<span style='color:green;'>审核已通过</span>";

                //todo 审核通过后置操作
                event('FlowDone', [$userArr, $data, $flowType]);
            }
        }elseif($result==2){
            //todo 转办 暂不考虑
            /*$is_transfer = $bill['now_step_id'];
            //获取办理人
            $uids = $transfer_uid;
            $nowStatusText = "转发给xx办理";
            $nextUser = ['user_id'=>$transfer_uid, 'user_name'=>$transfer_uname];*/
        }elseif($result==-1){
            $doneTime = time();
            //拒绝则通知申请人
            $uids = $bill['user_id'];
            //标记为拒绝
            if($bill['mtable']=='case_info'){
                if($flowType=='case_start'){
                    //新业务
                    Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                        'flow_status'=>config('code.FLOW_REJECT'),
                        'update_time'=>time()
                    ]);
                }elseif($flowType=='case_end'){
                    //结案

                }elseif($flowType=='case_tmp_money'){
                    //临时应收金额
                    Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                        'tmp_money_status'=>config('code.TMP_MONEY_FAIL'),
                        'update_time'=>time()
                    ]);
                }
            }else{
                Db::name($bill['mtable'])->where('id', $bill['mid'])->update([
                    'flow_status'=>config('code.FLOW_REJECT'),
                    'update_time'=>time()
                ]);
            }

            $nowStatusText = "<span style='color:red;'>申请已拒绝</span>";
        }elseif($result==-2){
            //退回到指定人
            //todo 暂仅支持直接退回至提交人，退至审核人无意义
            $is_back = 1;
            /*$process = FlowProcess::get($back_process_id);
            $uids = $process['user_id'];
            $nextStepId = $process['now_step_id']; //todo 获取 next
            //回滚状态
            $nowStatusText = "退回到".$process['user_name']."处理";
            $nextUser = ['user_id'=>$process['user_id'], 'user_name'=>$process['user_name']];*/

            $uids = $bill['user_id'];
            $nowStatusText = "退回到".$bill['user_name']."处理";
            $nextStepId = 0;
            $nextUser = ['user_id'=>$bill['user_id'], 'user_name'=>$bill['user_name']];
            $data['title'] = "案件退回修改";
            $data['message'] = "申请被退回，请修改后重新提交";
        }

        //写入process表
        $res = FlowProcess::insertProcess($bill['flow_id'], $bill['id'], $nextStepId, $bill['now_step_id'], $nowStatusText, $result, $nextUser['user_id'], $nextUser['user_name'], $data['attach_ids'], $data['remark'], $is_back, $is_transfer);

        //标记当前process为已处理
        Todo::where('bill_id', $bill_id)->update([
            'done_time'=>time()
        ]);

        //更新当前process
        FlowProcess::where('id', $process_id)->update([
            'deal_time'=>time(),
            'result_status'=>$result
        ]);

        //更新当前bill步骤
        FlowBill::where('id', $bill_id)->update([
            'now_step_id'=>$nextStepId,
            'now_checker_id'=>$nextUser['user_id'],
            'now_checker_name'=>$nextUser['user_name'],

            //todo 暂不考虑
            'done_time'=>$doneTime,
            'now_status_id'=>0,
            'back_to_process'=>0,
            'update_uid'=>0,
            'update_uname'=>'',
        ]);

        //判断是否需添加新todo 插入待办工作
        Todo::insertTodo($uids, $data['title'], $data['message'], $data['mtable'], $data['mid'], $data['muuid'], $data['need_do'], $data['url'], $bill_id, $userArr);

        //通知
        $noticeData = [];
        self::sendNotice($uids, $noticeData, $step['is_sms'], $step['is_wechat']);

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

    /**
     * 消息通知
     * @param string $uids 被通知人uid 1,2,4
     * @param array $data 通知内容 ['sms'=>[], 'wechat'=>[]]
     * @param int $isSms 是否短信
     * @param int $isWechat 是否微信
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function sendNotice($uids='', $data=[], $isSms=0, $isWechat=0)
    {
        //todo 待开启
        return true;

        if(!$uids) return true;
        //通知 依据是否通知 写入队列
        $noticeArr = User::where('id', 'in', $uids)->field('id,phone,name,openid')->select();
        if($isSms){
            foreach ($noticeArr as $v){
                if(!$v['phone']) continue;
                $smsData = [];
                Queue::instance()->do('doJob')->job(SmsJob::class)->data(true, $v['phone'], '', $smsData)->push();
            }
        }
        if($isWechat){
            foreach ($noticeArr as $v){
                if(!$v['openid']) continue;
                $tplData = [];
                Queue::instance()->do('sendFlowSuccess')->job(WechatTemplateJob::class)->data($v['openid'], $tplData)->push();
            }
        }
        //WebSocket推送
        $uidsArr = explode(',', $uids);
        GatewayClientService::sendToUid($uidsArr, ['type'=>'notice', 'data'=>'test']);
    }




}
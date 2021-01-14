<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2021/1/4
 * Time: 11:02
 */

namespace sensen\subscribes;


use think\facade\Db;

class FlowSubscribe
{
    public function handle()
    {

    }

    /**
     * 流程审批结束
     * @param $event
     */
    public function onFlowDone($event)
    {
        list($userInfo, $data) = $event;

        $flow = Db::name('flow')->where('id', $data['flow_id'])->find();

        //todo 一个表可能对应多种审批
        if($flow['type']=='case_start'){
            //todo 生成案件编号
            file_put_contents('./test.txt', 'case_start end '.$data['mid'], FILE_APPEND);

            //修改案件状态为：办理中


        }


    }

}
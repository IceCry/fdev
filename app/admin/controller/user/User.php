<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/22
 * Time: 15:45
 */
namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\models\auction\Deposit;
use app\models\collection\Collection;
use app\models\auction\Bid;
use app\models\auction\DepositRecharge;
use app\models\order\Order;
use app\models\user\User as UserModel;
use app\models\user\UserAddress;
use app\models\user\UserBill;
use app\models\wechat\Routine;
use sensen\services\JsonService;
use sensen\services\UtilService;
use think\facade\Db;

class User extends AuthController
{
    public function index()
    {
        $province = $this->getCityData(0);
        $this->assign('province', $province);
        return $this->fetch();
    }

    /**
     * 获取数据
     */
    public function getData()
    {
        $where = UtilService::getMore([
            ['province', ''],
            ['city', ''],
            ['district', ''],
            ['keyword', ''],
            ['page', 1],
            ['limit', 20],
            ['excel', 0],
            ['role', 0],
            ['field', ''],
            ['order', '']
        ]);
        return JsonService::successlayui(UserModel::getDataList($where));
    }

    /**
     * 会员出价记录
     * @param int $user_id
     */
    public function bidHistory($user_id=0)
    {
        return $this->fetch();
    }

    /**
     * 获取竞拍信息
     * @param int $user_id
     */
    public function getBidHistoryData($user_id=0)
    {
        //获取出价产品
        $collectionIds = Bid::where(['user_id'=>$user_id])->order('id desc')->group('collection_id')->column('collection_id');

        $data = [];
        foreach ($collectionIds as $k=>$v){
            $data[$k] = Collection::where('id', $v)->field('id,type,auction_type,title,max_price,thumb')->find()->toArray();

            $bids = Bid::where(['user_id'=>$user_id, 'collection_id'=>$v])->field('id,user_id,price,collection_id,create_time,is_offline')->select()->toArray();
            $bid_str = '';
            foreach ($bids as $x){
                $bid_str .= '【'.($x['is_offline']?'线下':'线上').'】'.'['.$x['create_time'].'] 出价:￥'.$x['price']."元<br/>";
            }
            $data[$k]['bid_str'] = $bid_str;
        }
        return JsonService::successlayui(0, $data);
    }

    /**
     * 用户保证金
     * @return string
     * @throws \Exception
     */
    public function deposit()
    {
        return $this->fetch();
    }

    /**
     * 获取保证金数据
     * @return mixed
     */
    public function getDepositData()
    {
        $where = UtilService::getMore([
            ['category','deposit'],
            ['user_id',0],
            ['page',1],
            ['limit',20]
        ]);

        return JsonService::successlayui(UserBill::getUserBillData($where));
    }

    /**
     * 用户详情
     * @param int $user_id
     * @return string
     * @throws \Exception
     */
    public function info($user_id=0)
    {
        //获取基础信息
        $info = UserModel::get($user_id)->toArray();

        //获取认证提交图片 仅获取通过认证的
        $cert_pic = Db::name('verify')->where(['user_id'=>$user_id, 'verify_status'=>1])->value('cert_pic');
        $info['cert_pic'] = $cert_pic?explode(',', $cert_pic):[];

        //保证金 当前 充值 提现
        $info['deposit'] = DepositRecharge::getUserDepositCount($user_id);

        //地址信息
        $info['address'] = UserAddress::where(['user_id'=>$user_id, 'delete_time'=>0])->select()->toArray();

        $this->assign(['info'=>$info]);
        return $this->fetch();
    }

    /**
     * 等待审核
     */
    public function verify()
    {
        return $this->fetch();
    }

    /**
     * 获取认证信息
     */
    public function getVerifyData()
    {
        $this->getPageSize(input('get.'));
        $count = Db::name('verify')->where(['verify_status'=>0, 'status'=>1])->count();
        $data = Db::name('verify')->where(['verify_status'=>0, 'status'=>1])->limit($this->from, $this->limit)->select()->toArray();

        foreach ($data as &$v){
            $v['role_name'] = get_user_role($v['role']);
            $v['create_time'] = date('Y/m/d H:i:s', $v['create_time']);
            $v['cert_pic'] = $v['cert_pic']?explode(',', $v['cert_pic']):[];
            $user = Db::name('user')->where('id', $v['user_id'])->field('id, nickname, avatar')->find();
            $v['nickname'] = $user['nickname'];
            $v['avatar'] = $user['avatar'];
            $v['cert_type'] = get_area($v['cert_type']);
        }

        return JsonService::successlayui(compact('count', 'data'));
    }

    /**
     * 审核操作
     * @param $id
     * @param $status 结果
     * @param string $remark 备注
     * @return array
     */
    public function doVerify($id, $status, $remark='')
    {
        //获取信息
        $info = Db::name('verify')->find($id);
        if(!$info){
            return show(1, '数据不存在');
        }
        if($status==-1 && !$remark){
            return show(1, '未填写拒绝理由');
        }
        //判断类型，根据类型处理表
        $verifyTitle = '实名认证';
        Db::startTrans();
        try{
            $userVerifyStatus = 0;
            $userData = [
                'is_verify'=>$userVerifyStatus
            ];
            if($status==1){
                //发送订阅消息
                $userVerifyStatus = 1;
                $userData = [
                    'real_name'=>$info['contact'],
                    'phone'=>$info['phone'],
                    'card_id'=>$info['cert_id'],
                    'is_verify'=>$userVerifyStatus
                ];
                Routine::sendVerifyResult($info['user_id'], $verifyTitle, '通过', '您已通过认证', '/pages/my/my');
            }elseif($status==-1){
                //拒绝 发送订阅消息
                Routine::sendVerifyResult($info['user_id'], $verifyTitle, '拒绝', $remark, '/pages/my/my');
            }

            Db::name('user')->where('id', $info['user_id'])->update($userData);
            Db::name('verify')->where('id', $id)->update(['verify_status'=>$status]);

            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            insert_log('认证失败:'.$e->getMessage(), 'user/doVerify', 6, $id, $this->userInfo['id'], $this->userInfo['name']);
            return show(1, '操作失败！错误：'.$e->getMessage());
        }
        insert_log('用户认证'.($status==1?'通过':'失败'), 'user/doVerify', 4, $id, $this->userInfo['id'], $this->userInfo['name']);
        return show(0, '操作成功');
    }

    /**
     * 修改保证金
     * @return string
     * @throws \Exception
     */
    public function depositEdit()
    {
        return $this->fetch();
    }

    /**
     * 更新保证金
     */
    public function updateDeposit()
    {
        //扣除需判断是否大于当前金额
        list($type, $price, $remark, $user_id) = UtilService::postMore([
            ['type', 0], ['price', 0], 'remark', 'user_id'
        ], null, true);

        if(!$user_id) return app('json')->fail('参数不足');
        //写入充值记录表
        $res = false;
        $tip = '';
        if($type==1){
            //充值
            $tip = '【充值】';
            $res = DepositRecharge::income($type, $user_id, $price, $remark, 0, 1, 1);
        }else if($type==-3){
            //系统扣除
            $tip = '【扣除】';
            $res = DepositRecharge::expend($type, $user_id, $price, $remark, 0, 1, 1);
        }

        if($res){
            insert_log($tip.'用户保证金'.$price, 'user/updateDeposit', 4, $user_id, $this->userInfo['id'], $this->userInfo['name']);
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 获取用户订单
     * @return string
     * @throws \Exception
     */
    public function order()
    {
        $order_status = $this->getDataValue('order_status');
        $this->assign(['order_status'=>$order_status]);
        return $this->fetch();
    }

    public function getOrderData()
    {
        $where = UtilService::getMore([
            ['from_type', ''],
            ['order_status', ''],
            ['range_time', ''],
            ['keyword', ''],
            ['user_id', ''],
            ['page', 1],
            ['limit', 20]
        ]);
        return JsonService::successlayui(Order::getOrderData($where));
    }

    /**
     * 获取xmSelect数据
     * @param string $keyword
     * @param integer $special_id
     */
    public static function getXmData($keyword='', $special_id=0)
    {
        $data = UserModel::getXmData($keyword, $special_id);

        return JsonService::success('ok', $data);
    }

}
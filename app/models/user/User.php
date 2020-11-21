<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/13
 * Time: 10:22
 */

namespace app\models\user;

use app\admin\model\system\Role;
use app\models\order\Order;
use sensen\basic\BaseModel;
use sensen\traits\JwtAuthModelTrait;
use sensen\traits\ModelTrait;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Session;

class User extends BaseModel
{
    use ModelTrait;
    use JwtAuthModelTrait;

    /**
     * 格式化地域编码
     * @param $value
     * @return array|mixed
     */
    public function getAreaCodeAttr($value)
    {
        return $value?json_decode($value, true):[11, 1101, 110101];
    }

    public function getGenderAttr($value)
    {
        $str = '未知';
        if($value==1){
            $str = '男';
        }elseif($value==2){
            $str = '女';
        }
        return $str;
    }

    /**
     * 获取用户默认地址
     * @param $value
     * @param $data
     */
    public function getAddressStrAttr($value, $data)
    {
        $address = Db::name('user_address')->where(['user_id'=>$data['id'], 'delete_time'=>0, 'status'=>1])->order('is_default desc')->find();
        if($address){
            return $address['province'].$address['city'].$address['district'].$address['detail'];
        }
        return '';
    }

    /**
     * 获取成交次数
     * @param $value
     * @param $data
     */
    public function getOrderNumAttr($value, $data)
    {
        return Order::where(['user_id'=>$data['id']])->where('pay_time', '>', 0)->count();
    }

    /**
     * 获取交易金额
     * @param $value
     * @param $data
     * @return float
     */
    public function getOrderPriceAttr($value, $data)
    {
        return Order::where(['user_id'=>$data['id']])->where('pay_time', '>', 0)->sum('total_price');
    }

    /**
     * 获取会员列表
     * @param $where
     * @return array
     */
    public static function getDataList($where)
    {
        $model = new self;
        if($where['keyword']){
            $model = $model->where('real_name|phone|nickname', 'like', "%".$where['keyword']."%");
        }
        if($where['province']){
            $cityName = get_city_name($where['province']);
            $model = $model->where('province', $cityName);
        }
        if($where['city']){
            $cityName = get_city_name($where['city']);
            $model = $model->where('city', $cityName);
        }
        if($where['district']){
            $cityName = get_city_name($where['district']);
            $model = $model->where('district', $cityName);
        }
        $count = $model->count();
        $model = $model->page((int)$where['page'], (int)$where['limit']);

        //是否排序
        if(isset($where['field']) && $where['field']){
            $order = "{$where['field']} {$where['order']}";
        }else{
            $order = 'id desc';
        }

        $data = $model->order($order)->append(['order_num', 'order_price'])->select()->toArray();
        return compact('count', 'data');
    }


    /**
     * 小程序授权登录
     * @param $routine
     * @return mixed
     */
    public static function routineOauth($routine)
    {
        $routineInfo['routine_openid'] = $routine['openId'];//openid
        $routineInfo['session_key'] = $routine['session_key'];//会话密匙
        $routineInfo['unionid'] = $routine['unionId'];//用户在开放平台的唯一标识符
        $routineInfo['user_type'] = 'routine';//用户类型

        //判断用户是否已存在，需使用unionid判断

        // 判断unionid  存在根据unionid判断
        if ($routineInfo['unionid'] != '' && ($uid = self::where(['unionid' => $routineInfo['unionid']])->value('id'))) {
            //此处不应修改原有信息
            self::edit($routineInfo, $uid, 'id');

        }elseif($uid = self::where(['routine_openid' => $routineInfo['routine_openid']])->value('id')){
            //todo 强制使用unionid 此处作废

            self::edit($routineInfo, $uid, 'id');
        } else {
            $routineInfo['nickname'] = $routine['nickName'];//姓名
            $routineInfo['gender'] = $routine['gender'];//性别
            //$routineInfo['language'] = $routine['language'];//语言
            $routineInfo['city'] = $routine['city'];//城市
            $routineInfo['province'] = $routine['province'];//省份
            //$routineInfo['country'] = $routine['country'];//国家
            $routineInfo['account'] = 'ss'.uniqid();

            //todo 头像需下载到本地
            $routineInfo['avatar'] = $routine['avatarUrl'];//头像

            $routineInfo['login_ip'] = request()->ip();
            $routineInfo['uuid'] = md5(uniqid());
            $res = self::create($routineInfo);
            $uid = $res->id;
        }
        return $uid;
    }

    /**
     * 根据uid获取openid
     * @param int $uid
     * @return mixed
     */
    public static function getOpenIdByUid($uid=0)
    {
        return self::where('id', $uid)->value('routine_openid');
    }

    /**
     * 获取用户信息
     * @param $uid
     */
    public static function getUserInfo($uid)
    {
        return self::where('id', $uid)->withoutField('account,password,salt,real_name,card_id,qq,wechat,create_time,update_time,delete_time,remark,login_time,login_ip,order_price,order_num')->find();
    }

    /**
     * 获取手机号是否绑定
     * @param $phone
     * @return bool
     */
    public static function checkPhone($phone)
    {
        return self::be(['phone' => $phone]);
    }

    /**
     * 获取xmSelect数据
     * @param string $keyword
     * @param integer $special_id
     * @return \think\Collection
     */
    public static function getXmData($keyword='', $special_id=0)
    {
        $data = self::where(['status'=>1, 'delete_time'=>0, 'is_verify'=>1])->append(['address_str'])->where('nickname|real_name|phone', 'like', "%{$keyword}%")->field('id, id as value, nickname as name, real_name, phone')->limit(50)->select();
        //如果为同步拍则仅获取缴纳保证金用户
        if($special_id>0){
            foreach ($data as $k=>$v){
                $hasDeposit = Db::name('deposit')->where(['user_id'=>$v['value'], 'type'=>2, 'deposit_status'=>0, 'special_id'=>$special_id, 'delete_time'=>0, 'status'=>1])->find();
                if(!$hasDeposit){
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }

}
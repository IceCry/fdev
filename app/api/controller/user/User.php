<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/5/26
 * Time: 14:48
 */

namespace app\api\controller\user;

use app\api\controller\Base;
use app\http\validates\RegisterValidates;
use app\models\article\Article;
use app\models\wechat\Routine;
use app\Request;
use sensen\services\CacheService;
use sensen\services\UtilService;
use app\models\user\User as UserModel;
use think\exception\ValidateException;
use think\facade\Db;

class User extends Base
{
    /**
     * 获取用户信息
     * @param Request $request
     * @return mixed
     */
    public function userInfo(Request $request)
    {
        $uid = $request->uid();

        $userInfo = $lawyerInfo = [];

        $userInfo = UserModel::where(['id'=>$uid])->withoutField('unionid, account, password, card_id, create_time, update_time, create_ip, last_time, last_ip')->find();

        //处理数据
        $userInfo['sex'] = get_sex($userInfo['gender']);
        $userInfo['region'] = $userInfo['area_code']?$userInfo['province'].'-'.$userInfo['city'].'-'.$userInfo['district']:'';
        //$userInfo['t_address'] = [$userInfo['province'], $userInfo['city'], $userInfo['district']];

        //todo 待删除 仅测试支付
        //$payment = RoutineService::pay($userInfo['routine_openid']);

        return app('json')->success(compact('userInfo', 'lawyerInfo'));
    }

    /**
     * 获取会员菜单
     * @param Request $request
     */
    public function getMyMenu(Request $request)
    {
        $user = $request->user();

        $menus = $this->getDataValue('my_menu');

        $role_user = config('web.ROLE_USER');
        $role_lawfirm = config('web.ROLE_LAWFIRM');
        $role_lawyer = config('web.ROLE_LAWYER');
        $role_company = config('web.ROLE_COMPANY');
        //如果不存在role则默认为普通用户
        $user['role'] = $user['role']?:$role_user;

        $userArr = $lawyerArr = $lawfirmArr = $companyArr = [];

        foreach ($menus as $v){
            $roleArr = explode(',', $v['role']);
            if(in_array($role_user, $roleArr)){
                $userArr[] = $v;
            }
            if(in_array($role_lawfirm, $roleArr)){
                $lawfirmArr[] = $v;
            }
            if(in_array($role_lawyer, $roleArr)){
                $lawyerArr[] = $v;
            }
            if(in_array($role_company, $roleArr)){
                $companyArr[] = $v;
            }
        }

        if($user['role']==$role_lawyer){
            //律师对应菜单
            $menus = $lawyerArr;
        }elseif($user['role']==$role_lawfirm){
            //律所 行政
            $menus = $lawfirmArr;
        }elseif($user['role']==$role_user){
            //普通用户
            $menus = $userArr;
        }elseif($user['role']==$role_company){
            $menus = $companyArr;
        }

        //数组按sort_num排序
        $sort_num = array_column($menus, 'sort_num');
        array_multisort($sort_num, SORT_DESC, $menus);

        //处理菜单是否有新消息等
        foreach ($menus as &$v) {
            $v['count'] = 0;
            if($v['url']=='/pages/my/ask'){
                //判断是否有新的解答 有则显示个数，无则显示箭头
                $v['count'] = Db::name('ask')->where('user_id', $user['id'])->sum('unread_answer');
            }else if($v['url']=='/pages/my/talk'){
                $v['arrow'] = false;
                if($user['role']==$role_lawyer){
                    $v['count'] = Db::name('talk_info')->where('lawyer_id', $user['role_mid'])->sum('lawyer_unread');
                }else{
                    $v['count'] = Db::name('talk_info')->where('user_id', $user['id'])->sum('user_unread');
                }
            }
            if($v['count']>0){
                $v['arrow'] = false;
            }else{
                $v['arrow'] = true;
            }
        }

        return app('json')->successful(compact('menus'));
    }

    /**
     * 绑定手机号
     * todo 暂不处理
     * @param Request $request
     */
    public function bindPhone(Request $request)
    {
        $user = $request->user();
        list($phone, $code) = UtilService::getMore([
            ['phone', ''],
            ['code', ''],
        ],$request, true);

        //验证码是否正确
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        $verifyCode = CacheService::get('code_'.$phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');


        //验证手机号是否已存在
        $has = UserModel::checkPhone($phone);
        if($has){
            return app('json')->fail('此手机号已被绑定');
        }
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $code){
            return app('json')->fail('验证码错误');
        }

        //判断是否被他人绑定
        if(UserModel::be(['phone'=>$phone])){
            return app('json')->fail('此手机号已被绑定，请更换');
        }
        $res = UserModel::where('id', $user['id'])->update(['phone'=>$phone]);
        if($res){
            //律师则同时修改律师手机号
            if($user['role'] == config('web.ROLE_LAWYER')){
                Db::name('lawyer')->where('id', $user['role_mid'])->update(['phone'=>$phone]);
            }
            return app('json')->success('绑定成功');
        }else{
            return app('json')->fail('绑定失败！请重试');
        }

    }

    /**
     * 修改用户基础信息
     * @param Request $request
     */
    public function editUser(Request $request)
    {
        $uid = $request->uid();
        list($nickname, $sex, $region, $address, $area_code) = UtilService::getMore([
            ['nickname', ''],
            ['sex', ''],
            ['region', ''],
            ['address', ''],
            ['area_code', []]
        ],$request, true);

        //处理sex
        $data = [
            'address'=>$address,
            'nickname'=>$nickname,
        ];
        $regionArr = explode('-', $region);
        $data['province'] = $regionArr[0];
        $data['city'] = $regionArr[1];
        $data['district'] = $regionArr[2];
        $data['update_time'] = time();

        //获取city_id
        $cityId = 0;
        if($area_code[1]){
            $fixCode = str_pad($area_code[1], 12, '0');
            $cityId = Db::name('city')->where('area_code', $fixCode)->value('city_id');
        }
        $data['city_id'] = $cityId;
        $data['area_code'] = json_encode($area_code);

        $res = UserModel::where('id', $uid)->update($data);

        if($res){
            return app('json')->success('操作成功');
        }else{
            return app('json')->fail('操作失败！请重试');
        }
    }


}
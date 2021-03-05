<?php
/**
 * Desc: 公共类
 * User: SenSen Wechat:1050575278
 * Date: 2020/5/26
 * Time: 13:35
 */

namespace app\api\controller;

use app\http\validates\RegisterValidates;
use app\models\system\Attachment;
use app\models\system\SmsHistory;
use app\models\user\User;
use sensen\repositories\SmsRepositories;
use sensen\services\CacheService;
use sensen\services\RoutineService;
use think\exception\ValidateException;
use think\facade\Cache;
use app\Request;
use think\facade\Config;
use think\facade\Db;

class Base
{
    /**
     * 首页数据
     * @return mixed
     */
    public function index()
    {
        //获取轮播
        $sliders = $this->getDataValue('home_slider');

        return app('json')->successful(compact('sliders'));
    }

    /**
     * 获取首页导航
     */
    public function getDataValue($type='')
    {
        if(!$type) return [];
        $gid = Db::name('data_group')->where('config_name', $type)->value('id');
        $lists = Db::name('data_group_value')->where(['gid'=>$gid, 'status'=>1])->select();

        $result = [];
        foreach ($lists as $v){
            $tmp = json_decode($v['value'], true);
            foreach ($tmp as $x=>$z){
                foreach ($z as $i=>$j){
                    $arr[$i] = $j['value'];
                }
            }
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * 获取城市信息
     * @param int $parent_id
     * @param bool $isAjax
     * @return array
     */
    public function getCityData($parent_id=0, $isAjax=false)
    {
        /*//判断是否缓存 缓存需区分城市级别
        if(Cache::has('city')){
            return Cache::get('city');
        }else{
            $city = Db::name('city')->select();
            Cache::set('city', $city);
            return $city;
        }*/
        $where = [];
        $where['status'] = 1;
        /*if($level!==''){
            $where['level'] = $level;
        }*/
        if($parent_id>0){
            $where['parent_id'] = $parent_id;
        }elseif($parent_id=='top'){
            $where['parent_id'] = 0;
        }
        $city = Db::name('city')->where($where)->order('sort_num desc')->select()->toArray();
        if($isAjax){
            return app('json')->successful(compact('city'));
        }else{
            return $city;
        }
    }

    /**
     * 生成短信key
     * key5分钟有效
     * @return mixed
     */
    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        Cache::set('sms.key.' . $unique, 0, 300);

        return app('json')->success(['key' => $unique]);
    }

    /**
     * 短信验证码
     * @param Request $request
     * @return mixed
     */
    public function sendSmsCode(Request $request)
    {
        list($phone, $type, $key, $code) = UtilService::postMore([['phone', ''], ['type', ''], ['key', ''], ['code', '']], $request, true);

        $keyName = 'sms.key.' . $key;
        $nowKey = 'sms.' . date('YmdHi');

        if (!Cache::has($keyName))
            return app('json')->make(401, '发送验证码失败');

        //暂不使用图形验证码
        if (($num = Cache::get($keyName)) > 2) {
            /*if (!$code)
                return app('json')->make(402, '请输入验证码');

            if (!$this->checkCaptcha($key, $code))
                return app('json')->fail('验证码输入有误');*/
        }

        $total = 1;
        if ($has = Cache::has($nowKey)) {
            $total = Cache::get($nowKey);
            if ($total > Config::get('sms.maxMinuteCount', 20))
                return app('json')->success('已发送');
        }

        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (User::checkPhone($phone) && ($type == 'register' || $type=='bind')) return app('json')->fail('手机号已注册');
        if (!User::checkPhone($phone) && $type == 'login') return app('json')->fail('账号不存在！');
        $default = Config::get('sms.default', 'aliyun');
        $defaultMaxPhoneCount = Config::get('sms.maxPhoneCount', 10);
        $defaultMaxIpCount = Config::get('sms.maxIpCount', 50);
        $maxPhoneCount = Config::get('sms.stores.' . $default . '.maxPhoneCount', $defaultMaxPhoneCount);
        $maxIpCount = Config::get('sms.stores.' . $default . '.maxIpCount', $defaultMaxIpCount);
        if (SmsHistory::where('phone', $phone)->where('add_ip', $request->ip())->whereDay('add_time')->count() >= $maxPhoneCount) {
            return app('json')->fail('您今日发送得短信次数已经达到上限');
        }
        if (SmsHistory::where('add_ip', $request->ip())->whereDay('add_time')->count() >= $maxIpCount) {
            return app('json')->fail('此IP今日发送次数已经达到上限');
        }
        $time = 300;
        if (CacheService::get('code_' . $phone))
            return app('json')->fail($time . '秒内有效');
        $code = rand(100000, 999999);
        $data['code'] = $code;
        $res = SmsRepositories::send(true, $phone, $data, 'VERIFICATION_CODE');
        if ($res !== true)
            return app('json')->fail('短信平台验证码发送失败' . $res);
        CacheService::set('code_' . $phone, $code, $time);
        Cache::set($keyName, $num + 1, 300);
        Cache::set($nowKey, $total, 61);

        return app('json')->success('发送成功');
    }

    /**
     * 更改头像
     * @param Request $request
     */
    public function avatarUpdate(Request $request)
    {
        $user = $request->user();
        $file = request()->file('avatar');
        // 上传到本地服务器
        $saveName = \think\facade\Filesystem::putFile('avatar', $file);

        if($saveName){
            $basePath = '/uploads/'.str_replace('\\', '/', $saveName);
            $saveName = set_web_url($basePath);

            //检测图片是否违规
            $checker = RoutineService::imgSecCheck('.'.$basePath);
            if($checker['errcode'] == '87014'){
                return app('json')->fail('图片包含违规内容，请修改');
            }

            Db::name('user')->where('id', $user['id'])->update(['avatar'=>$saveName]);

            if($user['role']==1){
                Db::name('lawyer')->where('user_id', $user['id'])->update(['avatar'=>$saveName]);
            }

            return app('json')->successful('操作成功', ['avatar'=>$saveName]);
        }else{
            return app('json')->fail('操作失败！请重试');
        }
    }

    /**
     * 上传附件到指定表
     * todo 此处为形象照上传，暂仅为图片
     * @param Request $request
     */
    public function attach(Request $request)
    {
        $file = request()->file('attach');
        // 上传到本地服务器
        $saveName = \think\facade\Filesystem::putFile('attach', $file);

        if($saveName){
            $basePath = '/uploads/'.str_replace('\\', '/', $saveName);
            $saveName = set_web_url($basePath);

            //检测图片是否违规
            $checker = RoutineService::imgSecCheck('.'.$basePath);
            if($checker['errcode'] == '87014'){
                return app('json')->fail('图片包含违规内容，请修改');
            }

            return app('json')->successful('操作成功', ['attach'=>$saveName]);
        }else{
            return app('json')->fail('操作失败！请重试');
        }
    }

    /**
     * 指定表增加指定字段数据
     * @param Request $request
     * @return mixed
     */
    public function setKeyInc(Request $request)
    {
        list($mtable, $mid, $key, $inc) = UtilService::postMore([
            ['mtable', ''],
            ['mid', 0],
            ['key', ''],
            ['inc', 1]
        ], $request, true);

        Db::name($mtable)->where('id', $mid)->inc($key, $inc)->update();
        return app('json')->successful('操作成功');
    }

    /**
     * 获取16地市
     * @return mixed
     */
    public function getCity()
    {
        $lists = $this->getDataValue('team_citys');
        return app('json')->successful(compact('lists'));
    }


}
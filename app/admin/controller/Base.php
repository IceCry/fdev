<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 8:29
 */

namespace app\admin\controller;

use app\Request;
use sensen\basic\BaseController;
use sensen\services\JsonService;
use sensen\services\RoutineService;
use think\facade\Db;

class Base extends BaseController
{
    public $page = 1;
    public $limit = 10;
    public $from = 0;

    protected function initialize()
    {
        parent::initialize();
        //获取domain判断是否合法
        /*$domain = request()->domain();
        if(!strstr($domain, 'guoui.com') && !strstr($domain, 'bjssgjpm.cn') && !strstr($domain, 'auction.com')){
            echo "<p style='text-align: center; margin-top: 200px;font-size: 30px;font-weight: 700;color: #ccc;'>:( 系统未授权！请联系微信：guoyouruanjian</p>";die;
        }
        //判断登录页是否被篡改
        $md5File = app_path().'/view/login/index.html';
        //echo md5_file($md5File);die;
        if(md5_file($md5File)!='a78f85a82b74f21711d2ca7d342b4dbf'){
            echo "<p style='text-align: center; margin-top: 200px;font-size: 30px;font-weight: 700;color: #ccc;'>:( 系统未授权！请联系微信：guoyouruanjian</p>";die;
        }*/
    }

    /**
     * 获取起始位置
     * @param  [type] $page [description]
     * @param  [type] $size [description]
     * @return [type]       [description]
     */
    public function getPageSize($data)
    {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->limit = !empty($data['limit']) ? $data['limit'] : config('web.list_size');
        $this->from = ($this->page - 1) * $this->limit;
    }

    /**
     * 操作失败提示框
     * @param string $msg 提示信息
     * @param string $backUrl 跳转地址
     * @param string $title 标题
     * @param int $duration 持续时间
     * @return mixed
     */
    protected function failedNotice($msg = '操作失败', $backUrl = 0, $info = '', $duration = 3)
    {
        $type = 'error';
        $this->assign(compact('msg', 'backUrl', 'info', 'duration', 'type'));
        return $this->fetch('public/notice');
    }

    /**
     * 失败提示一直持续
     * @param $msg
     * @param int $backUrl
     * @param string $title
     * @return mixed
     */
    protected function failedNoticeLast($msg = '操作失败', $backUrl = 0, $info = '')
    {
        return $this->failedNotice($msg, $backUrl, $info, 0);
    }

    /**
     * 操作成功提示框
     * @param string $msg 提示信息
     * @param string $backUrl 跳转地址
     * @param string $title 标题
     * @param int $duration 持续时间
     * @return mixed
     */
    protected function successfulNotice($msg = '操作成功', $backUrl = 0, $info = '', $duration = 3)
    {
        $type = 'success';
        $this->assign(compact('msg', 'backUrl', 'info', 'duration', 'type'));
        return $this->fetch('public/notice');
    }

    /**
     * 成功提示一直持续
     * @param $msg
     * @param int $backUrl
     * @param string $title
     * @return mixed
     */
    protected function successfulNoticeLast($msg = '操作成功', $backUrl = 0, $info = '')
    {
        return $this->successfulNotice($msg, $backUrl, $info, 0);
    }

    /**
     * 错误提醒页面
     * @param string $msg
     * @param int $url
     */
    protected function failed($msg = '哎呀…亲…您访问的页面出现错误', $url = 0)
    {
        if ($this->request->isAjax()) {
            exit(JsonService::fail($msg, $url)->getContent());
        } else {
            $this->assign(compact('msg', 'url'));
            exit($this->fetch('public/error'));
        }
    }

    /**
     * 成功提醒页面
     * @param string $msg
     * @param int $url
     */
    protected function successful($msg, $url = 0)
    {
        if ($this->request->isAjax()) {
            exit(JsonService::successful($msg, $url)->getContent());
        } else {
            $this->assign(compact('msg', 'url'));
            exit($this->fetch('public/success'));
        }
    }

    /**异常抛出
     * @param $name
     */
    protected function exception($msg = '无法打开页面')
    {
        $this->assign(compact('msg'));
        exit($this->fetch('public/exception'));
    }

    /**找不到页面
     * @param $name
     */
    public function _empty($name)
    {
        exit($this->fetch('public/404'));
    }

    /**
     * 通用更新状态
     * @param string $table
     * @param int $id
     * @param int $status
     * @param string $field
     * @return array
     */
    public function updateStatus($table='', $id=0, $status=0, $field='status')
    {
        if(!in_array($field, ['status', 'is_check', 'open', 'flow_status', 'is_public', 'sort_num', 'click', 'collect_status', 'remark', 'fictitious_sales', 'stock', 'flag'])){
            return show(1, '操作失败！');
        }
        $res = Db::name($table)->where('id', $id)->update([$field=>$status]);
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败');
        }
    }

    /**
     * 获取配置信息
     * @param string $type
     * @return array
     */
    public function getDataValue($type='')
    {
        if(!$type) return [];
        $gid = Db::name('data_group')->where('config_name', $type)->value('id');
        $lists = Db::name('data_group_value')->where(['gid'=>$gid, 'status'=>1])->select();

        $result = [];
        foreach ($lists as $v){
            $arr = [];
            $tmp = json_decode($v['value'], true);
            foreach ($tmp as $x=>$z){
                foreach ($z as $i=>$j){
                    $arr[$i] = $j['value'];
                }
            }
            if($arr['status']==1){
                $result[] = $arr;
            }
        }
        return $result;
    }

    /**
     * 根据组合数据id获取对应name
     * @param string $type
     * @param int $id
     * @return string
     */
    public function getDataNameById($type='', $id=0)
    {
        $str = '';
        $data = $this->getDataValue($type);
        foreach ($data as $v){
            if($v['id'] == $id){
                $str = $v['name'];
                break;
            }
        }
        return $str;
    }

    /**
     * 获取树状目录
     * @param $cates
     * @param int $pid
     * @return array
     */
    public function getTree($cates, $pid = 0){
        $tree = array();
        foreach($cates as $v) {
            if ($v['pid'] == $pid) {
                $tree[] = $v;
                $tree = array_merge($tree, $this->getTree($cates, $v['id']));
            }
        }
        return $tree;
    }

    /**
     * 目录名加前缀
     * @param $data
     * @param string $p
     * @return array
     */
    public function setPrefix($data, $p = "|---")
    {
        $tree = [];
        $num = 1;
        $prefix = [0 => 1];
        while($val = current($data)) {
            $key = key($data);
            if ($key > 0) {
                if ($data[$key - 1]['pid'] != $val['pid']) {
                    $num ++;
                }
            }
            if (array_key_exists($val['pid'], $prefix)) {
                $num = $prefix[$val['pid']];
            }
//            $val['org_name'] = $val['name'];
            $val['name_str'] = str_repeat($p, $num).$val['name'];
            $prefix[$val['pid']] = $num;
            $tree[] = $val;
            next($data);
        }
        return $tree;
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
        $city = Db::name('city')->where($where)->select()->toArray();
        if($isAjax){
            return JsonService::success('ok', $city);
        }else{
            return $city;
        }
    }

    /**
     * 根据省份获取城市
     * @param int $province_id
     */
    public function getCityByPid($city_id=0)
    {
        $str = '<option value="">请选择城市</option>';
        $citys = Db::name('city')->where(['parent_id'=>$city_id, 'status'=>1])->select();
        foreach ($citys as $v){
            $str .= '<option value="'.$v['city_id'].'">'.$v['name'].'</option>';
        }
        echo $str;
    }

    /**
     * 上传附件到指定表
     * @param Request $request
     * @param string $type
     * @return mixed
     */
    public function attach($type='')
    {
        $file = request()->file('file');
        // 上传到本地服务器
        $saveName = \think\facade\Filesystem::putFile('admin', $file);
        $resData = [];

        if($saveName){
            $basePath = '/uploads/'.str_replace('\\', '/', $saveName);
            $saveName = set_web_url($basePath);

            //获取身份证信息
            if($type == 'cert_front'){
                //获取身份证信息
                $resData = RoutineService::ocr($saveName);
            }

            return app('json')->successful('操作成功', ['url'=>$saveName, 'data'=>$resData]);
        }else{
            return app('json')->fail('操作失败！请重试');
        }
    }


}
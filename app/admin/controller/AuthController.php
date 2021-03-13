<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 11:07
 */

namespace app\admin\controller;

use app\admin\model\system\Admin;
use app\admin\model\system\Role;
use sensen\services\DataService;
use sensen\services\JsonService;
use think\facade\Db;
use think\facade\Route;
use sensen\services\upload\Upload;
use app\models\system\Attachment;

class AuthController extends Base
{
    //当前用户登录信息
    protected $userInfo;

    //当前用户登录id
    protected $uid;

    //当前用户权限
    protected $auth = [];

    //当前用户最高角色（可能包含多个角色）
    protected $topRole = '';

    //城市信息
    public $city = '';

    /**
     * 构造方法
     */
    protected function initialize()
    {
        parent::initialize();
        if(!Admin::isOnline()){
            return $this->redirect(Route::buildUrl('login/index')->suffix(false)->build());
        }
        //获取用户信息
        //判断当前用户是否被禁用 考虑cookie存储时间 防止session过期无法获取userInfo
        try{
            $userInfo = Admin::getUserInfoOrFail();
        }catch (\Exception $e){
            return $this->failed(Admin::getErrorInfo($e->getMessage()), Route::buildUrl('login/index')->suffix(false)->build());
        }

        $this->uid = $userInfo['id'];
        $this->userInfo = $userInfo->toArray();
        $this->auth = Admin::getUserAuth($this->userInfo);
        //判断是否授权
        $this->isSuperAdmin() || $this->checkAuth();
        $this->assign(['userInfo'=>$this->userInfo, 'city'=>$this->city]);
        //$this->assign('menu', $this->getMenu());
        //dump($this->getMenu());die;
        //记录访问日志
        //event('AdminVisit', [$this->userInfo]);
    }

    /**
     * 获取用户对应菜单
     * @return mixed
     */
    public function getMenu()
    {
        $data = Admin::getUserMenu($this->uid);
        return DataService::channelLevel($data, 0, '&nbsp;', 'id');
    }

    /**
     * 检测是否有权限访问
     * @param string $name
     * @return bool
     */
    protected function checkAuth($name='')
    {
        static $allAuth = null;
        if($allAuth === null){
            $allAuth = Role::getAllRule();
        }
        if(!$name){
            $module = app('http')->getName();
            $controller = $this->request->controller();
            $action = $this->request->action();
            $name = $module.'/'.$controller.'/'.$action;
        }
        $name = strtolower($name);
        if(in_array($name, $allAuth) && !in_array($name, $this->auth)){
            die($this->failed('您没有权限访问！'));
        }
        return true;
    }

    /**
     * 判断是否为超级管理员
     */
    protected function isSuperAdmin()
    {
        if(in_array($this->uid, config('web.super_admin'))){
            return true;
        }
        return false;
    }

    /**
     * 基础上传弹窗
     * @return string
     * @throws \Exception
     */
    public function upload()
    {
        return $this->fetch('public/upload');
    }

    /**
     * 基础上传
     * @param string $type 类型
     * @param string $name_origin 附件名称
     * @param string $from_id
     * @param int $cate_id 分类
     * @param string $mtable 中间表
     * @param int $mid 中间表id
     */
    public function baseUpload($type='', $name_origin='', $from_id='', $cate_id=0, $mtable='', $mid=0)
    {
        $upload_type = $this->request->get('upload_type', sys_config('upload_type', 1));
        try {
            $path = make_path('attach', 2, true);
            $upload = new Upload((int)$upload_type, [
                'accessKey' => sys_config('accessKey'),
                'secretKey' => sys_config('secretKey'),
                'uploadUrl' => sys_config('uploadUrl'),
                'storageName' => sys_config('storage_name'),
                'storageRegion' => sys_config('storage_region'),
            ]);
            //验证类型
            $res = $upload->to($path)->validate()->move('file');
            if ($res === false) {
                return JsonService::fail('上传失败：' . $upload->getError());
            } else {
                $aid = 0;
                $fileInfo = $upload->getUploadInfo();
                if ($fileInfo) {
                    $attachInfo = Attachment::attachmentAdd($type, $fileInfo['name'], $name_origin, $fileInfo['dir'], $fileInfo['ext'], $fileInfo['mime'], $fileInfo['size'], $fileInfo['path_zip'], $mid, $cate_id, $this->uid, $mtable, $fileInfo['hash'], $upload_type);
                    $aid = $attachInfo->id;
                }
                return JsonService::successful('上传成功', ['src' => $res->filePath, 'aid'=>$aid, 'name_origin'=>$name_origin]);
            }
        } catch (\Exception $e) {
            return JsonService::fail('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 附件预览
     * todo 依据不同文件对应展示
     * @param string $uuid
     */
    public function preview($uuid='')
    {
        if(!$uuid){
            return $this->failed('附件不存在');
        }
    }

    /**
     * 强制下载
     * @param string $fileName
     * @param string $uuid 如果存在uuid则获取对应filename
     * @return bool|false|int
     */
    public function download($fileName='', $uuid='')
    {
        if($uuid){
            $path = Attachment::where('uuid', $uuid)->value('path');
            $fileName = "." . $path;
        }

        if (false == file_exists($fileName)) {
            return false;
        }
        // http headers
        header('Content-Type: application-x/force-download');
        header('Content-Disposition: attachment; filename="' . basename($fileName) .'"');
        header('Content-length: ' . filesize($fileName));

        // for IE6
        if (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
            header('Cache-Control: no-cache, must-revalidate');
        }
        header('Pragma: no-cache');

        // read file content and output
        return readfile($fileName);
    }

    /**
     * 获取当前用户最高权限
     */
    public function getTopRole()
    {

    }

    /**
     * 判断用户是否拥有此rule权限
     * @param string $rule 待检测权限，必须存在且
     * @return bool
     */
    public function hasAuth($rule='')
    {
        if(!$rule) return true;

        $ruleInfo = Db::name('auth_rule')->where('name', $rule)->find();
        if(!$ruleInfo) return true;
        if($ruleInfo['is_check']==0 || $ruleInfo['status']==0) return true;

        if(in_array($rule, $this->auth)){
            return true;
        }
        return false;
    }

}
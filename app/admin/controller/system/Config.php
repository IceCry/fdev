<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:45
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use app\models\system\{
    Config as ConfigModel, ConfigTab
};
use sensen\services\FileService;
use sensen\services\JsonService;

class Config extends AuthController
{
    /**
     * 首页配置
     * @return string
     */
    public function index()
    {
        $tabs = ConfigTab::getAllTab()->toArray();
        //获取tab对应配置
        foreach ($tabs as &$v){
            $configs = ConfigModel::where(['config_tab_id'=>$v['id'], 'status'=>1])->select();
            $v['configs'] = $configs;
        }
        $this->assign(['tabs'=>$tabs]);
        return $this->fetch();
    }

    public function save()
    {
        $data = input('post.');
        ConfigModel::updateMultiValue($data);
        return JsonService::successful('操作成功');
    }

    /**
     * 删除缓存
     */
    public function clear()
    {
        return $this->fetch();
    }

    public function clearData()
    {
        FileService::del_dir('../runtime/cache');
        insert_log('清除缓存', 'config/clearData', 1, '', $this->userInfo['id'], $this->userInfo['name']);
        return JsonService::success('操作成功');
    }

}
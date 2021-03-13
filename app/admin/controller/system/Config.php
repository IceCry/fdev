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

            //判断config类型
            foreach ($configs as &$x){
                $x['value'] = json_decode($x['value']);
                if($x['type']=='radio'){
                    //radio则获取描述内容为可选值
                    $tmp = explode(',', $x['desc']);
                    $item = [];
                    foreach ($tmp as $t){
                        $tmp2 = explode('|', $t);
                        $item[] = ['name'=>$tmp2[0], 'value'=>$tmp2[1]];
                    }
                    $x['items'] = $item;
                }
            }

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
        return JsonService::success('操作成功');
    }

}
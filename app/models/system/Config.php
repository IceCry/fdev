<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 14:57
 */

namespace app\models\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;

class Config extends BaseModel
{
    use ModelTrait;

    /**
     * 修改单个配置
     * @param $menu
     * @param $value
     * @return bool
     */
    public static function setValue($menu, $value)
    {
        if (empty($menu) || !($config_one = self::get(['menu_name' => $menu]))) return self::setErrorInfo('字段名称错误');
        if ($config_one['type'] == 'radio' || $config_one['type'] == 'checkbox') {
            $parameter = [];
            $option = [];
            $parameter = explode(',', $config_one['parameter']);
            foreach ($parameter as $k => $v) {
                if (isset($v) && !empty($v)) {
                    $option[$k] = explode('-', $v);
                }
            }
            $value_arr = [];//选项的值
            foreach ($option as $k => $v) {
                foreach ($v as $kk => $vv)
                    if (!$kk) {
                        $value_arr[$k] = $vv;
                    }
            }
            $i = 0;//
            if (is_array($value)) {
                foreach ($value as $value_v) {
                    if (in_array($value_v, $value_arr)) {
                        $i++;
                    }
                }
                if (count($value) != $i) return self::setErrorInfo('输入的值不属于选项中的参数');
            } else {
                if (in_array($value, $value_arr)) {
                    $i++;
                }
                if (!$i) return self::setErrorInfo('输入的值不属于选项中的参数');
            }
            if ($config_one['type'] == 'radio' && is_array($value)) return self::setErrorInfo('单选按钮的值是字符串不是数组');
        }
        $bool = self::edit(['value' => json_encode($value)], $menu, 'menu_name');
        return $bool;
    }

    /**
     * 批量修改配置
     * @param $data
     * @return bool
     */
    public static function updateMultiValue($data)
    {
        foreach ($data as $k=>$v){
            self::where('menu_name', $k)->update(['value'=>json_encode($v)]);
        }
        return true;
    }

    /**
     * 获取单个参数配置
     * @param $menu
     * @return bool|mixed
     */
    public static function getConfigValue($menu)
    {
        if (empty($menu) || !($config_one = self::where('menu_name', $menu)->find())) return false;
        return json_decode($config_one['value'], true);
    }

    /**
     * 获得多个参数
     * @param $menus
     * @return array
     */
    public static function getMore($menus)
    {
        $menus = is_array($menus) ? implode(',', $menus) : $menus;
        $list = self::where('menu_name', 'IN', $menus)->column('value', 'menu_name') ?: [];
        foreach ($list as $menu => $value) {
            $list[$menu] = json_decode($value, true);
        }
        return $list;
    }

    /**
     * @return array
     */
    public static function getAllConfig()
    {
        $list = self::column('value', 'menu_name') ?: [];
        foreach ($list as $menu => $value) {
            $list[$menu] = json_decode($value, true);
        }
        return $list;
    }

    /**
     * 获取一数据
     * @param $filed
     * @param $value
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOneConfig($filed, $value)
    {
        $where[$filed] = $value;
        return self::where($where)->find();
    }

    /**
     * 获取配置分类
     * @param $id
     */
    public static function getAll($id, int $status = 0)
    {
        $where['config_tab_id'] = $id;
        if ($status == 1) $where['status'] = $status;
        return self::where($where)->order('sort_num desc,id asc')->select();
    }

    /**
     * 获取所有配置分类
     * @param int $type
     * @return array
     */
    public static function getConfigTabAll($type = 0)
    {
        $configAll = ConfigTab::getAll($type);
        $config_tab = [];
        foreach ($configAll as $k => $v) {
            if (!$v['info']) {
                $config_tab[$k]['id'] = $v['id'];
                $config_tab[$k]['label'] = $v['title'];
                $config_tab[$k]['icon'] = $v['icon'];
                $config_tab[$k]['type'] = $v['type'];
                $config_tab[$k]['pid'] = $v['pid'];

            }
        }
        return $config_tab;
    }

    /**
     * 获取所有配置分类
     * @param int $type
     * @return array
     */
    public static function getConfigChildrenTabAll($pid = 0)
    {
        $configAll = ConfigTab::getChildrenTab($pid);
        $config_tab = [];
        foreach ($configAll as $k => $v) {
            if (!$v['info']) {
                $config_tab[$k]['id'] = $v['id'];
                $config_tab[$k]['label'] = $v['title'];
                $config_tab[$k]['icon'] = $v['icon'];
                $config_tab[$k]['type'] = $v['type'];
                $config_tab[$k]['pid'] = $v['pid'];
            }
        }
        return $config_tab;
    }

    /**
     * 配置短信信息
     * @param $account
     * @param $token
     * @return bool
     */
    public static function setConfigSmsInfo($account, $token)
    {
        self::beginTrans();
        $res1 = self::where('menu_name', 'sms_account')->where('value', '"' . $account . '"')->count();
        if (!$res1) $res1 = self::where('name', 'sms_account')->update(['value' => '"' . $account . '"']);
        $res2 = self::where('menu_name', 'sms_token')->where('value', '"' . $token . '"')->count();
        if (!$res2) $res2 = self::where('name', 'sms_token')->update(['value' => '"' . $token . '"']);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

}
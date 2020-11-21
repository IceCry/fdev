<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/4
 * Time: 9:51
 */

namespace app\admin\model\system;


use sensen\basic\BaseModel;
use sensen\traits\ModelTrait;
use think\facade\Db;
use think\model\concern\SoftDelete;

class Role extends BaseModel
{
    use ModelTrait;
    use SoftDelete;

    protected $name = 'auth_group';

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    /**
     * 获取角色数据
     */
    public static function getRoleData()
    {
        $model = new self;
        $data = $model->where(['delete_time'=>0])->order('sort_num desc')->select();
        $count = count($data);
        return compact('count', 'data');
    }

    /**
     * 获取所有权限
     * 仅获取需验证的权限
     */
    public static function getAllRule($is_menu=false)
    {
        if($is_menu){
            return Rule::where(['is_check'=>1, 'is_menu'=>1, 'status'=>1])->order('sort_num desc')->select()->toArray();
        }else{
            $rules = Rule::where(['is_check'=>1, 'status'=>1])->order('sort_num desc')->select()->toArray();
            return self::tidyRule($rules);
        }
    }

    /**
     * 获取指定角色对应的权限id
     * @param string $roles 角色格式1,3
     * @param bool $reverse 是否为未授权的权限
     * @return array
     */
    public static function getRoleRuleIds($roles='', $reverse=false)
    {
        $rules = self::where('id', 'in', $roles)->column('rules');
        $ruleArr = [];
        foreach ($rules as $v){
            if($v){
                $tmp = explode(',', $v);
                $ruleArr = array_merge($ruleArr, $tmp);
            }
        }
        $rules = array_unique($ruleArr);

        if($reverse){
            //获取所有权限
            $ruleAll = Rule::where(['is_check'=>1, 'status'=>1])->column('id');
            //取差集
            $rules = array_diff($ruleAll, $rules);
        }

        return $rules;
    }

    /**
     * 获取指定用户权限
     * @param string $roles
     * @param int $user_id
     */
    public static function getUserRule($roles='', $user_id=0, $is_menu=false)
    {
        $roleRuleIds = self::getRoleRuleIds($roles);
        $additionalRule = Db::name('additional_rule')->where(['type'=>'user', 'mid'=>$user_id])->value('rules');
        $addRules = explode(',', $additionalRule);
        $rulesIds = array_merge($roleRuleIds, $addRules);
        $rulesIds = array_unique($rulesIds);
        $rules = Rule::where('id', 'in', $rulesIds)->select()->toArray();
        if($is_menu){
            foreach ($rules as $k=>$v){
                if($v['is_menu']==0){
                    unset($rules[$k]);
                }
            }
            return $rules;
        }
        return self::tidyRule($rules);
    }

    /**
     * 格式化用户权限为id=>name
     * @param $data
     * @return array
     */
    public static function tidyRule($data)
    {
        $auth = [];
        foreach ($data as $k=>$v){
            $auth[$v['id']] = $v['name'];
        }
        return $auth;
    }



}
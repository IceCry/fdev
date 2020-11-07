<?php

namespace sensen\basic;

use think\facade\Db;
use think\Model;

class BaseModel extends Model
{
    private static $errorMsg;

    private static $transaction = 0;

    private static $DbInstance = [];

    const DEFAULT_ERROR_MSG = '操作失败,请稍候再试!';

    /**
     * 设置错误信息
     * @param string $errorMsg
     * @param bool $rollback
     * @return bool
     */
    protected static function setErrorInfo($errorMsg = self::DEFAULT_ERROR_MSG, $rollback = false)
    {
        if ($rollback) self::rollbackTrans();
        self::$errorMsg = $errorMsg;
        return false;
    }

    /**
     * 获取错误信息
     * @param string $defaultMsg
     * @return string
     */
    public static function getErrorInfo($defaultMsg = self::DEFAULT_ERROR_MSG)
    {
        return !empty(self::$errorMsg) ? self::$errorMsg : $defaultMsg;
    }

    /**
     * 开启事务
     */
    public static function beginTrans()
    {
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public static function commitTrans()
    {
        Db::commit();
    }

    /**
     * 关闭事务
     */
    public static function rollbackTrans()
    {
        Db::rollback();
    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     */
    public static function checkTrans($res)
    {
        if ($res) {
            self::commitTrans();
        } else {
            self::rollbackTrans();
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
     * 获取dataValue指定id对应的name
     * @param string $type
     * @param int $id
     * @param string $field
     */
    public function getDataValueName($type='', $id=0, $field='id')
    {
        $str = '';
        $data = $this->getDataValue($type);
        foreach ($data as $v){
            if($v[$field] == $id){
                $str = $v['name'];
                break;
            }
        }
        return $str;
    }

}
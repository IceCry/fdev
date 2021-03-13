<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace sensen\services;

use think\facade\Config;

/**
 * Class UtilService
 * @package sensen\services
 */
class UtilService
{
    /**
     * 获取POST请求的数据
     * @param $params
     * @param null $request
     * @param bool $suffix
     * @return array
     */
    public static function postMore($params, $request = null, $suffix = false)
    {
        if ($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param) {
            if (!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            } else {
                if (!isset($param[1])) $param[1] = null;
                if (!isset($param[2])) $param[2] = '';
                if (is_array($param[0])) {
                    $name = is_array($param[1]) ? $param[0][0] . '/a' : $param[0][0] . '/' . $param[0][1];
                    $keyName = $param[0][0];
                } else {
                    $name = is_array($param[1]) ? $param[0] . '/a' : $param[0];
                    $keyName = $param[0];
                }
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $keyName)] = $request->param($name, $param[1], $param[2]);
            }
        }
        return $p;
    }

    /**
     * 获取请求的数据
     * @param $params
     * @param null $request
     * @param bool $suffix
     * @return array
     */
    public static function getMore($params, $request = null, $suffix = false)
    {
        if ($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param) {
            if (!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            } else {
                if (!isset($param[1])) $param[1] = null;
                if (!isset($param[2])) $param[2] = '';
                if (is_array($param[0])) {
                    $name = is_array($param[1]) ? $param[0][0] . '/a' : $param[0][0] . '/' . $param[0][1];
                    $keyName = $param[0][0];
                } else {
                    $name = is_array($param[1]) ? $param[0] . '/a' : $param[0];
                    $keyName = $param[0];
                }
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $keyName)] = $request->param($name, $param[1], $param[2]);
            }
        }
        return $p;
    }

    /**
     * TODO 修改 https 和 http 移动到common
     * @param $url $url 域名
     * @param int $type 0 返回https 1 返回 http
     * @return string
     */
    public static function setHttpType($url, $type = 0)
    {
        $domainTop = substr($url, 0, 5);
        if ($type) {
            if ($domainTop == 'https') $url = 'http' . substr($url, 5, strlen($url));
        } else {
            if ($domainTop != 'https') $url = 'https:' . substr($url, 5, strlen($url));
        }
        return $url;
    }

    /**分级返回下级所有分类ID
     * @param $data
     * @param string $children
     * @param string $field
     * @param string $pk
     * @return string
     */
    public static function getChildrenPid($data, $pid, $field = 'pid', $pk = 'id')
    {
        static $pids = '';
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $pids .= ',' . $res[$pk];
                self::getChildrenPid($data, $res[$pk], $field, $pk);
            }
        }
        return $pids;
    }
}

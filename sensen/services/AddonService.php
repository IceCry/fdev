<?php
/**
 * Desc: 插件服务
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/15
 * Time: 16:26
 */

namespace sensen\services;

use think\Exception;
use think\facade\Db;

class AddonService
{
    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return config('web.api_url');
    }

    /**
     * 下载插件包
     * @param string $name 插件名
     * @param array $extend 附加参数
     */
    public static function download($name='', $extend=[])
    {
        $addonTmpDir = app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR;
        if(!is_dir($addonTmpDir)){
            @mkdir($addonTmpDir, 0755, true);
        }
        $tmpFile = $addonTmpDir . $name . '.zip';

        $options = [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];

        $ret = DownloadService::sendRequest(self::getServerUrl() . '/addon/download', array_merge(['name' => $name], $extend), 'GET', $options);
        if ($ret['ret']) {
            if (substr($ret['msg'], 0, 1) == '{') {
                $json = (array)json_decode($ret['msg'], true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    array_pop($options);
                    $ret = DownloadService::sendRequest($json['data']['url'], [], 'GET', $options);
                    if (!$ret['ret']) {
                        //下载返回错误，抛出异常
                        exception($json['msg'], $json['code'], $json['data']);
                    }
                } else {
                    //下载返回错误，抛出异常
                    exception($json['msg'], $json['code'], $json['data']);
                }
            }
            if ($write = fopen($tmpFile, 'w')) {
                fwrite($write, $ret['msg']);
                fclose($write);
                return $tmpFile;
            }
            throw new Exception("没有权限写入临时文件");
        }
        throw new Exception("无法下载远程文件");
    }

    /**
     * 解压插件
     * @param string $name 插件名称
     */
    public static function unzip($name='')
    {
        $file = app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR . $name . '.zip';
        $dir = app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($file) !== TRUE) {
                throw new Exception('Unable to open the zip file');
            }
            if (!$zip->extractTo($dir)) {
                $zip->close();
                throw new Exception('Unable to extract the file');
            }
            $zip->close();
            return $dir;
        }
        throw new Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }

    /**
     * 备份插件
     * @param $name 插件名
     */
    public function backup($name='')
    {
        $file = app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR . $name . '-backup-' . date("YmdHis") . '.zip';
        $dir = app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zip->open($file, \ZipArchive::CREATE);
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $info) {
                $filePath = $info->getPathName();
                $localName = str_replace($dir, '', $filePath);
                if ($info->isFile()) {
                    $zip->addFile($filePath, $localName);
                } elseif ($info->isDir()) {
                    $zip->addEmptyDir($localName);
                }
            }
            $zip->close();
            return true;
        }
        throw new Exception("无法执行压缩操作，请确保ZipArchive安装正确");
    }

    /**
     * 安装插件
     * @param   string $name 插件名称
     * @param   boolean $force 是否覆盖
     * @param   array $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     */
    public static function install($name, $force = false, $extend = [])
    {
        if (!$name || (is_dir(app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR . $name) && !$force)) {
            throw new Exception('插件已存在，请卸载后重装');
        }

        // 远程下载插件
        $tmpFile = self::download($name, $extend);

        // 解压插件
        $addonDir = self::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        try {
            // 检查插件是否完整
            self::check($name);

            if (!$force) {
                self::noconflict($name);
            }
        } catch (\Exception $e) {
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        }

        // 复制文件
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, $destAssetsDir);
        }
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                copydirs($addonDir . $dir, app()->getRootPath() . $dir);
            }
        }

        try {
            // 默认启用该插件
            $info = get_addons_info($name);
            if (!$info['state']) {
                $info['state'] = 1;
                self::setInfo($name, $info);
            }

            // 执行安装脚本
            $class = get_addons_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 导入
        self::importsql($name);

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 卸载插件
     * @param   string $name
     * @param   boolean $force 是否强制卸载
     * @return  boolean
     * @throws  Exception
     */
    public static function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            self::noconflict($name);
        }

        // 移除插件基础资源目录
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($destAssetsDir)) {
            rmdirs($destAssetsDir);
        }

        // 移除插件全局资源文件
        if ($force) {
            $list = self::getGlobalFiles($name);
            foreach ($list as $k => $v) {
                @unlink(app()->getRootPath() . $v);
            }
        }

        // 执行卸载脚本
        try {
            $class = get_addons_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->uninstall();
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 移除插件目录
        rmdirs(app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR . $name);

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 升级插件
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     */
    public static function upgrade($name, $extend = [])
    {
        /*$info = get_addon_info($name);
        if ($info['state']) {
            throw new Exception(__('Please disable addon first'));
        }
        $config = get_addon_config($name);
        if ($config) {
            //备份配置
        }*/

        // 备份插件文件
        self::backup($name);

        // 远程下载插件
        $tmpFile = self::download($name, $extend);

        // 解压插件
        $addonDir = self::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        /*if ($config) {
            // 还原配置
            set_addon_config($name, $config);
        }*/

        // 导入
        self::importsql($name);

        // 执行升级脚本
        try {
            $class = get_addons_class($name);
            if (class_exists($class)) {
                $addon = new $class();

                if (method_exists($class, "upgrade")) {
                    $addon->upgrade();
                }
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        self::refresh();

        return true;
    }

    /**
     * 导入sql
     * @param $name
     */
    public function importSql($name)
    {
        $sqlFile = app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $tempLine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $tempLine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $tempLine = str_ireplace('__PREFIX__', config('database.prefix'), $tempLine);
                    $tempLine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $tempLine);
                    try {
                        Db::getPdo()->exec($tempLine);
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $tempLine = '';
                }
            }
        }
        return true;
    }

    /**
     * todo 刷新插件缓存信息
     * @return bool|void
     * @throws Exception
     */
    public static function refresh()
    {
        return true;
    }


    /**
     * todo 设置基础配置信息
     */
    public static function setInfo()
    {
        return true;
    }

    /**
     * 检测插件是否完整
     * @param string $name
     */
    public static function check($name='')
    {
        return true;
    }

    /**
     * 检测是否有冲突
     * @param string $name 插件名称
     */
    public static function noConflict($name='')
    {
        //检测重复文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            exception("发现冲突文件", -3, ['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 获取插件在全局的文件
     * @param $name 插件名称
     * @param bool $onlyConflict 是否仅检测冲突
     * @return array
     */
    public static function getGlobalFiles($name, $onlyConflict = false)
    {
        $list = [];
        $addonDir = app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        // 扫描插件目录是否有覆盖的文件
        foreach (self::getCheckDirs() as $k => $dir) {
            $checkDir = app()->getRootPath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
            if (!is_dir($checkDir))
                continue;
            //检测到存在插件外目录
            if (is_dir($addonDir . $dir)) {
                //匹配出所有的文件
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($addonDir . $dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $info) {
                    if ($info->isFile()) {
                        $filePath = $info->getPathName();
                        $path = str_replace($addonDir, '', $filePath);
                        if ($onlyConflict) {
                            $destPath = app()->getRootPath() . $path;
                            if (is_file($destPath)) {
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $path;
                                }
                            }
                        } else {
                            $list[] = $path;
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取插件类的类名
     * @param string $name  插件名
     * @param string $type  返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    public static function get_addon_class($name, $type = 'hook', $class = null)
    {
        $name = parse_name($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = parse_name(end($class), 1);
            $class = implode('\\', $class);
        } else {
            $class = parse_name(is_null($class) ? $name : $class, 1);
        }
        switch ($type) {
            case 'controller':
                $namespace = "\\addons\\" . $name . "\\controller\\" . $class;
                break;
            default:
                $namespace = "\\addons\\" . $name . "\\" . $class;
        }
        return class_exists($namespace) ? $namespace : '';
    }

    /**
     * 获取插件源资源文件夹
     * @param   string $name 插件名称
     * @return  string
     */
    protected static function getSourceAssetsDir($name)
    {
        return app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取插件目标资源文件夹
     * @param   string $name 插件名称
     * @return  string
     */
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = app()->getRootPath() . str_replace("/", DIRECTORY_SEPARATOR, "public/static/addons/{$name}/");
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        return $assetsDir;
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'application',
            'public'
        ];
    }


}
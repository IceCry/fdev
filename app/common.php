<?php
// 应用公共文件

/**
 * 生成uuid 暂使用md5
 */
function create_uuid()
{
    return md5(uniqid());
}

/**
 * 密码加密
 * @param $pwd
 * @param $salt
 * @return string
 */
function en_pwd($pwd, $salt)
{
    return md5($pwd . '-' . $salt);
}

/**
 * 抛出异常处理
 *
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @param string $exception 异常类
 *
 * @throws Exception
 */
function exception($msg, $code = 0, $exception = '')
{
    $e = $exception ?: '\think\Exception';
    throw new $e($msg, $code);
}

/**
 * 删除内容链接
 * @param $content
 * @param string $domain 白名单
 * @return mixed
 */
function remove_link($content, $domain='www.r1989.com')
{
    preg_match_all('/href="(.*?)"/',$content,$matches);
    if($matches){
        foreach($matches[1] as $val){
            if( strpos($val,$domain)===false )
                $content = str_replace('href="'.$val.'"', '',$content);
        }
    }
    return $content;
}

/**
 * 字符串中间隐藏
 * @param string $string 需要替换的字符串
 * @param int $start 开始的保留几位
 * @param int $end 最后保留几位
 * @return string
 */
function str_middle_replace($string, $start, $end)
{
    $strlen = mb_strlen($string, 'UTF-8');//获取字符串长度
    $firstStr = mb_substr($string, 0, $start, 'UTF-8');//获取第一位
    $lastStr = mb_substr($string, -1, $end, 'UTF-8');//获取最后一位
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($string, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

}

/**
 * CURL 检测远程文件是否在
 * @param $url
 * @return bool
 */
function curl_file_exist($url)
{
    $ch = curl_init();
    try {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $contents = curl_exec($ch);
        if (preg_match("/404/", $contents)) return false;
        if (preg_match("/403/", $contents)) return false;
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 获取系统单个配置
 * @param string $name
 * @param string $default
 * @return string
 */
function sys_config(string $name, $default = '')
{
    if (empty($name))
        return $default;
    $config = trim(app('sysConfig')->get($name));
    if ($config === '' || $config === false) {
        return $default;
    } else {
        return $config;
    }
}

/**
 * 设置附加路径
 * @param $url
 * @return bool
 */
function set_web_url($url, $siteUrl = '')
{
    if(!$url) return '';
    if (!strlen(trim($siteUrl))) $siteUrl = sys_config('site_url');
    $domainTop = substr($url, 0, 4);
    if ($domainTop == 'http') return $url;
    $url = str_replace('\\', '/', $url);
    return $siteUrl . $url;
}

/**
 * 匿名处理处理用户昵称
 * @param $name
 * @return string
 */
function anonymity($name)
{
    $strLen = mb_strlen($name, 'UTF-8');
    $min = 3;
    if ($strLen <= 1)
        return '*';
    if ($strLen <= $min)
        return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $min - 1);
    else
        return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $strLen - 1) . mb_substr($name, -1, 1, 'UTF-8');
}

/**
 * 人性化时间
 * @param $timestamp
 * @return false|string
 */
function human_date($timestamp) {
    $seconDIRECTORY_SEPARATOR = time() - $timestamp;
    if($seconDIRECTORY_SEPARATOR > 31536000) {
        return date('Y-n-j', $timestamp);
    } elseif($seconDIRECTORY_SEPARATOR > 2592000) {
        return floor($seconDIRECTORY_SEPARATOR / 2592000).'月前';
    } elseif($seconDIRECTORY_SEPARATOR > 86400) {
        return floor($seconDIRECTORY_SEPARATOR / 86400).'天前';
    } elseif($seconDIRECTORY_SEPARATOR > 3600) {
        return floor($seconDIRECTORY_SEPARATOR / 3600).'小时前';
    } elseif($seconDIRECTORY_SEPARATOR > 60) {
        return floor($seconDIRECTORY_SEPARATOR / 60).'分钟前';
    } else {
        return $seconDIRECTORY_SEPARATOR.'秒前';
    }
}

/**
 * 转化content相对路径为绝对路径
 * @param string $content
 * @return mixed
 */
function update_content_url($content='')
{
    return str_replace('src="/uploads/', 'src="'.sys_config('site_url').'/uploads/', $content);
}

/**
 * 过滤数据
 * @param $data
 * @return string
 */
function filter_data($data)
{
    return htmlspecialchars(strip_tags($data));
}

/**
 * 字符串截取，支持中文和其他编码
 * static 
 * access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true, $strip_tags=false)
{
    if($strip_tags){
        $str = strip_tags($str);//去除html标记
        $pattern = "/&[a-zA-Z]+;/";//去除特殊符号
        $str = preg_replace($pattern,'',$str);
    }

    if(function_exists("mb_substr")){
        $slice = mb_substr($str, $start, $length, $charset);
    }elseif(function_exists("iconv_substr")){
        $slice = iconv_substr($str, $start, $length, $charset);
        if(false === $slice){
            $slice = '';
        }
    }else{
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 获取时间范围 laydate range
 * @param string $time
 * @param boolean $day
 */
function get_range_time($time='', $day=true)
{
    $timeArr = explode(' - ', $time);
    $st = strtotime($timeArr[0]);
    $et = strtotime($timeArr[1]);
    if($day){
        $et = $et + 86400;
    }
    return ['st'=>$st, 'et'=>$et, 'st_str'=>$timeArr[0], 'et_str'=>$timeArr[1]];
}

/**
 * 加密解密
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return bool|string
 */
function auth_code($string, $operation = 'DECODE', $expiry = 0, $key='') {
    $key = $key ? $key : config('web.auth_key');
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;

    // 密匙
    $key = md5($key);

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length):
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 * 获取星期几
 * @param      string  $day    日期
 * @return     array   星期
 */
function get_weekday($day=''){
    $day = $day ? $day : date('Y-m-d');
    $weekArr=array("日","一","二","三","四","五","六");
    return $weekArr[date("w", strtotime($day))];
}

/**
 * html转纯文本
 * @param      <type>  $str    html字符串
 */
function html2text($str){
    $str = strip_tags($str);
    $str = str_replace('&nbsp;', '', $str);
    return $str;
}

/**
 * 强制下载，如pdf等
 * @param      <type>   $filename  The filename
 * @return     boolean  ( description_of_the_return_value )
 */
function force_download($filename) {
    if (false == file_exists($filename)) {
        return false;
    }
    // http headers
    header('Content-Type: application-x/force-download');
    header('Content-Disposition: attachment; filename="' . basename($filename) .'"');
    header('Content-length: ' . filesize($filename));

    // for IE6
    if (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
        header('Cache-Control: no-cache, must-revalidate');
    }
    header('Pragma: no-cache');

    // read file content and output
    return readfile($filename);
}

/**
 * 上传路径转化,默认路径
 * @param $path
 * @param int $type 1年份 2年月 3年月日
 * @param bool $force
 * @return string
 */
function make_path($path, int $type = 2, bool $force = false)
{
    $path = DIRECTORY_SEPARATOR . ltrim(rtrim($path));
    switch ($type) {
        case 1:
            $path .= DIRECTORY_SEPARATOR . date('Y');
            break;
        case 2:
            $path .= DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
            break;
        case 3:
            $path .= DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
            break;
    }
    try {
        if (is_dir(app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . $path) == true || mkdir(app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . $path, 0777, true) == true) {
            return trim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '.');
        }else{
            return '';
        };
    } catch (\Exception $e) {
        if ($force){
            throw new \Exception($e->getMessage());
        }
        return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'attach' . DIRECTORY_SEPARATOR;
    }
}

/**
 * 删除文件夹
 * @param string $dirname  目录
 * @param bool   $withself 是否删除自身
 * @return boolean
 */
function rmdirs($dirname, $withself = true)
{
    if (!is_dir($dirname)) {
        return false;
    }
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    if ($withself) {
        @rmdir($dirname);
    }
    return true;
}

/**
 * 复制文件夹
 * @param string $source 源文件夹
 * @param string $dest   目标文件夹
 */
function copydirs($source, $dest)
{
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item
    ) {
        if ($item->isDir()) {
            $sontDir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if (!is_dir($sontDir)) {
                mkdir($sontDir, 0755, true);
            }
        } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}

/**
 * 隐藏手机号中间
 * @param $tel
 * @param int $len
 * @return mixed
 */
function hide_tel($tel, $len=4)
{
    $strLen = strlen($tel);
    $start = floor(($strLen - $len)/2);
    return substr_replace($tel, str_repeat('*', $len), $start, $len);
}

/**
 * 获取城市名称
 * @param $value
 */
function get_city_name($value)
{
    return \think\facade\Db::name('city')->where('city_id', $value)->value('name');
}

/**
 * 格式化文件大小
 * @param $b
 * @param int $times
 * @return string
 */
function get_human_size($b, $times=0)
{
    if($b>1024){
        $temp=$b/1024;
        return get_human_size($temp, $times+1);
    }else{
        $unit='B';
        switch($times){
            case '0':$unit='B';break;
            case '1':$unit='KB';break;
            case '2':$unit='MB';break;
            case '3':$unit='GB';break;
            case '4':$unit='TB';break;
            case '5':$unit='PB';break;
            case '6':$unit='EB';break;
            case '7':$unit='ZB';break;
            default: $unit='单位未知';
        }
        return sprintf('%.2f',$b).$unit;
    }
}

/**
 * 日志等级
 */
function get_log_level($status)
{
    $str = '';
    switch($status){
        case '1':
            $str='<span class="layui-badge layui-bg-gray">普通</span>';
            break;
        case '4':
            $str='<span class="layui-badge layui-bg-black">提示</span>';
            break;
        case '6':
            $str='<span class="layui-badge layui-bg-orange">警告</span>';
            break;
        case '9':
            $str='<span class="layui-badge">严重</span>';
            break;
        default:
            break;
    }
    return $str;
}

/**
 * 错误信息记录
 * @param $msg
 * @param $mvc
 * @param $level
 * @param $user_id
 * @param $link_id
 * @param $user_name
 * @param int $is_admin
 * @return int|string
 */
function insert_log($msg, $mvc, $level, $link_id, $user_id, $user_name, $is_admin=1)
{
    return \think\facade\Db::name('log')->insertGetId([
        'user_id'=>$user_id,
        'user_name'=>$user_name,
        'create_time'=>time(),
        'create_ip'=>request()->ip(),
        'agent'=>request()->header('user-agent'),
        'mvc'=>$mvc,
        'message'=>$msg,
        'level'=>$level,
        'is_admin'=>$is_admin,
        'link_id'=>$link_id
    ]);
}

/**
 * 获取性别
 * @param $status
 * @return string
 */
function get_sex($status){
    $str = '';
    switch ($status) {
        case 0:
            $str = '保密';
            break;
        case 1:
            $str = '男';
            break;
        case 2:
            $str = '女';
            break;
        default:
            break;
    }
    return $str;
}

/**
 * 获取审核人员类型
 * @param int $status
 * @return string
 */
function get_checker_type($status=0)
{
    switch ($status) {
        case 'all':
            $str = '全体人员';
            break;
        case 'super':
            $str = '直属上级';
            break;
        case 'dept':
            $str = '部门';
            break;
        case 'role':
            $str = '角色';
            break;
        case 'user':
            $str = '指定人员';
            break;
        case 'dept_leader':
            $str = '部门负责人';
            break;

        default:
            $str = '';
            break;
    }
    return $str;
}

/**
 * 获取差多少天
 * @param $endTime
 * @param $startTime
 */
function get_diff_day($endTime=0, $startTime=0)
{
    if(!$startTime) $startTime = time();
    $lefts = $endTime - $startTime;
    $days = ceil($lefts/86400);

    if($lefts>0){
        $str = "<span class='text-success text-semibold'>$days</span>";
    }else{
        $str = "<span class='text-danger text-semibold'>$days</span>";
    }
    return $str;
}

/**
 * 转小时为分钟
 * @param int $hour
 * @param int $min
 * @return float|int
 */
function hour_to_min($hour=0, $min=0){
    return $hour*60 + $min;
}

/**
 * 获取人员类型
 * @param string $type
 */
function get_user_type_name($type='')
{
    $str = '';
    switch ($type) {
        case 'all':
            $str = '全体人员';
            break;
        case 'dept':
            $str = '指定部门';
            break;
        case 'role':
            $str = '指定角色';
            break;
        case 'user':
            $str = '指定人员';
            break;
        case 'super':
            $str = '直属上级';
            break;
        case 'dept_leader':
            $str = '部门负责人';
            break;
    }
    return $str;
}

/**
 * 判断服务器是windows还是类unix
 *
 * @return     string  ( description_of_the_return_value )
 */
function linux_or_win(){
    $str = strtoupper(substr(PHP_OS,0,3));
    if($str == 'WIN'){
        return 'win';
    }else{
        return 'unix';
    }
}

/**
 * 将日期设为汉字模式
 * 2016-11-23
 */
function set_date_cn($str){
    $month = str_split(date('m',strtotime($str)));
    $month = implode('十', $month);
    $month = str_replace('十0', '十', $month);
    $month = str_replace('1十', '十', $month);
    $month = str_replace('0十', '', $month);

    $day = str_split(date('d',strtotime($str)));
    $day = implode('十', $day);
    $day = str_replace('十0', '十', $day);
    $day = str_replace('1十', '十', $day);
    $day = str_replace('0十', '', $day);

    return str_replace(str_split('0123456789'), str_split('〇一二三四五六七八九',3), date('Y',strtotime($str)).'年'.$month.'月'.$day).'日';
}
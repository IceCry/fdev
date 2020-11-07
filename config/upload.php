<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/11/7
 * Time: 16:01
 */

return [
    //默认上传模式
    'default' => 'local',
    //上传文件大小
    'filesize' => 2097152,
    //上传文件后缀类型
    'fileExt' => ['jpg', 'jpeg', 'png', 'gif', 'pem', 'mp3', 'wma', 'wav', 'amr', 'mp4', 'docx', 'zip', 'rar', '7z', 'pdf', 'xls', 'xlsx', 'doc', 'ppt', 'txt', 'avi', 'mov'],
    //上传文件类型
    'fileMime' => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/gif', 'text/plain', 'audio/mpeg', 'audio/x-wav', 'video/mp4', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/zip', 'video/quicktime', 'video/x-msvideo'],
    //驱动模式
    'stores' => [
        //本地上传配置
        'local' => [],
        //七牛云上传配置
        'qiniu' => [],
        //oss上传配置
        'oss' => [],
        //cos上传配置
        'cos' => [],
    ]
];
<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/9
 * Time: 16:03
 */

namespace app\services;

use sensen\services\SpreadsheetExcelService;

class ExportService extends BaseService
{
    /**
     * 数据导出
     * @param $header 表头
     * @param $title_arr 标题
     * @param array $export 填充数据
     * @param string $filename 保存文件名
     * @param string $suffix 文件后缀
     * @param bool $is_save 是否保存到本地
     */
    public function export($header, $title_arr, $export = [], $filename = '', $suffix = 'xlsx', $is_save = false)
    {
        $title = isset($title_arr[0]) && !empty($title_arr[0]) ? $title_arr[0] : '导出数据';
        $name = isset($title_arr[1]) && !empty($title_arr[1]) ? $title_arr[1] : '导出数据';
        $info = isset($title_arr[2]) && !empty($title_arr[2]) ? $title_arr[2] : date('Y-m-d H:i:s', time());

        $path = SpreadsheetExcelService::instance()->setExcelHeader($header)
            ->setExcelTile($title, $name, $info)
            ->setExcelContent($export)
            ->excelSave($filename, $suffix, $is_save);
        $path = $this->siteUrl() . $path;
        return [$path];
    }

    /**
     * 获取网址
     * @return string
     */
    public function siteUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }

    /**
     * 用户导出
     * @param $data 导出数据
     */
    public function user($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['nickname'],
                    $item['sex'],
                    $item['country'] . $item['province'] . $item['city'],
                    $item['subscribe'] == 1 ? '关注' : '未关注',
                ];
            }
        }
        $header = ['名称', '性别', '地区', '是否关注公众号'];
        $title = ['微信用户导出', '微信用户导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '微信用户导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        return $this->export($header, $title, $export, $filename, $suffix);
    }

    /**
     * 导出日程
     * @param array $data
     * @return array
     */
    public function memo($data = [])
    {
        $export = [];
        if(!empty($data)){
            foreach ($data as $v){
                $export[] = [
                    $v['id'],
                    $v['type_str'],
                    $v['user_name'],
                    $v['range_time'],
                    $v['address'],
                    $v['case_num'],
                    $v['customer_name'],
                    $v['flow_status'],
                    $v['create_time']
                ];
            }
        }
        $header = ['ID', '类型', '录入人', '起止时间', '地点', '关联案件', '关联客户', '当前状态', '创建时间'];
        $title = ['日程', '日程', date('Y-m-d H:i:s', time())];
        $filename = '日程_' . date('YmdHis', time());
        $suffix = 'xlsx';
        return $this->export($header, $title, $export, $filename, $suffix);
    }

}
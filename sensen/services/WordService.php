<?php
/**
 * Desc: word处理
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/12
 * Time: 17:06
 */

namespace sensen\services;

use PhpOffice\PhpWord\TemplateProcessor;

class WordService
{
    private $path;
    private $caseInfo;
    private $id;

    private function __construct(){}



    /**
     * 模版数据渲染
     * @param $tpl 模版文件名
     * @param $data
     * @param $savePath 文件存储路径
     */
    public static function tpl($tpl, $data, $savePath)
    {
        $templateProcessor = new TemplateProcessor('./tpl/case/'.$tpl);
        foreach ($data as $k=>$v){
            if($v['type']=='text'){
                $templateProcessor->setValue($k, $v['value']);
            }elseif($v['type']=='image'){
                $templateProcessor->setImageValue($k, ["path" => $v['value'], "width" => $v['width'], "height" => $v['height']]);
            }elseif($v['type']=='clone'){
                if(array_type($v['value'])==1){
                    //一维数组
                    $num = count($v['value']);
                    $templateProcessor->cloneRow($k, $num);
                    for ($i=0; $i<$num; $i++){
                        //暂定全为text
                        $templateProcessor->setValue($k.'#'.($i+1), $v['value'][$i]);
                    }
                }else{
                    //二维数组，暂定全为二维
                    foreach ($v['value'] as $x=>$y){
                        $num = count($y);
                        $templateProcessor->cloneRow($x, $num);
                        for ($i=0; $i<$num; $i++){
                            //暂定全为text
                            $templateProcessor->setValue($x.'#'.($i+1), $y['value'][$i]);
                        }
                    }
                }
            }
        }
        $templateProcessor->saveAs($savePath);
    }

}
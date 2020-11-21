<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/22
 * Time: 9:07
 */

namespace app\admin\controller\widget;

use app\models\system\Attachment;
use sensen\services\ConfigService;
use sensen\basic\BaseController;
use sensen\services\JsonService;

class Api extends BaseController
{
    public function index()
    {
        return 111;
    }
    /**
     * 微信上传图片
     * @return string
     * @throws \Exception
     */
    public function wxUploadImage()
    {
        $siteUrl = ConfigService::get('site_url');

        $this->assign(compact('siteUrl'));
        return $this->fetch();
    }

    /**
     * 检测微信传图结果
     * @param string $from_id 来源id 即mid
     */
    public function checkWxUpload($from_id='')
    {
        $data = Attachment::where('from_id', $from_id)->field('id as aid, path as src, name')->select()->toArray();
        if($data){
            //拼装
            return JsonService::success('存在上传文件', $data);
        }else{
            return JsonService::fail('未检测到上传文件');
        }
    }

}
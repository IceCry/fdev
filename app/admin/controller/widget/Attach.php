<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/4/10
 * Time: 16:05
 */
namespace app\admin\controller\widget;

use app\models\system\Attachment;
use app\models\system\AttachmentCategory;
use app\admin\controller\AuthController;
use sensen\services\JsonService;
use sensen\services\upload\Upload;
use sensen\services\UtilService;
use think\facade\Db;

class Attach extends AuthController
{
    /**
     * 获取图片分类
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        //获取分类
        $cate_id = request()->param('cate_id');
        if ($cate_id === NULL) {
            $cate_id = session('cate_id') ? session('cate_id') : 0;
        }
        session('cate_id', $cate_id);
        $this->assign('cate_id', $cate_id);

        return $this->fetch();
    }

    /**
     * 获取图片列表
     */
    public function getImageData()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 18],
            ['cate_id', 0]
        ]);
        return JsonService::successful(Attachment::getImageList($where));
    }

    /**
     * 图片上传
     */
    public function upload()
    {
        $cate_id = $this->request->param('cate_id', session('cate_id'), 0);
        $thumb = $this->request->param('thumb', '');
        $type = $this->request->param('type', 'image');
        $mId = $this->request->param('mid', '');
        $mtable = $this->request->param('mtable', '');
        $upload_type = $this->request->get('upload_type', sys_config('upload_type', 1));
        try {
            $path = make_path('attach', 2, true);
            $upload = new Upload((int)$upload_type, [
                'accessKey' => sys_config('accessKey'),
                'secretKey' => sys_config('secretKey'),
                'uploadUrl' => sys_config('uploadUrl'),
                'storageName' => sys_config('storage_name'),
                'storageRegion' => sys_config('storage_region'),
            ]);
            //验证类型
            $res = $upload->to($path)->validate()->move('file', $thumb);
            if ($res === false) {
                return JsonService::fail('上传失败：' . $upload->getError());
            } else {
                $fileInfo = $upload->getUploadInfo();
                if ($fileInfo) {
                    Attachment::attachmentAdd($type, $fileInfo['name'], $fileInfo['name_origin'], $fileInfo['dir'], $fileInfo['ext'], $fileInfo['mime'], $fileInfo['size'], $fileInfo['path_zip'], $mId, $cate_id, $this->uid, $mtable, $fileInfo['hash'], $upload_type);
                }
                return JsonService::successful('上传成功', ['src' => $res->filePath]);
            }
        } catch (\Exception $e) {
            return JsonService::fail('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        $data = input('post.');
        if (empty($data['imageId']))
            return JsonService::fail('请先选择图片');
        foreach ($data['imageId'] as $v) {
            if ($v) self::deleteImageData($v);
        }
        return JsonService::successful('删除成功');
    }

    /**
     * 删除图片和数据记录
     * todo 权限验证
     * @param $id
     */
    public function deleteImageData($id=0)
    {
        $info = Attachment::get($id);
        if ($info) {
            try {
                $upload = new Upload((int)$info['type'], [
                    'accessKey' => sys_config('accessKey'),
                    'secretKey' => sys_config('secretKey'),
                    'uploadUrl' => sys_config('uploadUrl'),
                    'storageName' => sys_config('storage_name'),
                    'storageRegion' => sys_config('storage_region'),
                ]);
                if ($info['type'] == 1) {
                    $upload->delete($info['path']);
                } else {
                    $upload->delete($info['name']);
                }
            } catch (\Throwable $e) {
            }
            Attachment::where('id', $id)->delete();
        }
    }

    /**
     * 添加图片分类
     */
    public function addCate()
    {
        //获取所有分类 暂仅支持两级
        $cates = AttachmentCategory::where(['status'=>1, 'pid'=>0])->select();
        $this->assign('cates', $cates);
        return $this->fetch('create_cate');
    }

    public function editCate()
    {
        return $this->fetch();
    }

    public function saveCate()
    {
        $data = input('post.');
        //检测数据合法
        $id = $data['id'];
        unset($data['id']);
        if($id){
            $res = AttachmentCategory::edit($data, $id, 'id');
        }else{
            $res = AttachmentCategory::create($data);
        }

        if($res){
            JsonService::successful('操作成功');
        }else{
            JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * 获取分类
     * @param string $name
     */
    public function getCateData($name='')
    {
        return JsonService::successful(AttachmentCategory::where(['status'=>1, 'delete_time'=>0])->where('name', 'like', "%$name%")->select());
    }

    public function deleteCate($cate_id=0)
    {
        $res = AttachmentCategory::del($cate_id);
        if($res){
            return JsonService::successful('操作成功');
        }else{
            return JsonService::fail('操作失败！请重试');
        }
    }

    /**
     * todo 功能待处理
     * 移动图片分类显示
     */
    public function moveImg($images)
    {

        $formbuider = [];
        $formbuider[] = Form::hidden('images', $images);
        $formbuider[] = Form::select('cate_id', '选择分类')->setOptions(function () {
            $list = Category::getCateList();
            $options = [['value' => 0, 'label' => '所有分类']];
            foreach ($list as $id => $cateName) {
                $options[] = ['label' => $cateName['html'] . $cateName['name'], 'value' => $cateName['id']];
            }
            return $options;
        })->filterable(1);
        $form = Form::make_post_form('编辑分类', $formbuider, Url::buildUrl('moveImgCecate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 移动图片分类操作
     */
    public function moveImgCecate()
    {
        $data = UtilService::postMore([
            'cate_id',
            'images'
        ]);
        if ($data['images'] == '') return Json::fail('请选择图片');
        if (!$data['cate_id']) return Json::fail('请选择分类');
        $res = SystemAttachmentModel::where('att_id', 'in', $data['images'])->update(['cate_id' => $data['cate_id']]);
        if ($res)
            Json::successful('移动成功');
        else
            Json::fail('移动失败！');
    }

    /**
     * 大文件上传
     * @return string
     */
    public function bigFile()
    {
        return $this->fetch();
    }

    /**
     * 上传文件函数，如过上传不成功打印$_FILES数组，查看error报错信息
     * 值：0; 没有错误发生，文件上传成功。
     * 值：1; 上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
     * 值：2; 上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
     * 值：3; 文件只有部分被上传。
     * 值：4; 没有文件被上传。
     */
    public function bigFileUpload($folder='video')
    {
        $url = sys_config('site_url');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-type: text/html; charset=gbk32");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        set_time_limit(0);

        $targetDir = app()->getRuntimePath() . 'file_material_tmp'; //存放分片临时目录
        $uploadDir = app()->getRootPath() . 'public/uploads/' . $folder . '/' . date('Ym');

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        // Create target dir
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $oldName = $fileName;

        $fileName = iconv('UTF-8', 'gb2312', $fileName);
        $filePath = $targetDir . '/' . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory111."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . '/' . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream222."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file333."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream444."}, "id" : "id"}');
            }
        } else {
            if (!$in = fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream555."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        fclose($out);
        fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }

        if ($done) {
            $pathInfo = pathinfo($fileName);
            $hashStr = substr(md5($pathInfo['basename']), 8, 16);
            $hashName = time() . $hashStr . '.' . $pathInfo['extension'];
            $uploadPath = $uploadDir .'/'. $hashName;
            if (!$out = fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream666."}, "id" : "id"}');
            }
            //flock($hander,LOCK_EX)文件锁
            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    unlink("{$filePath}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            fclose($out);
            $response = [
                'success' => true,
                'oldName' => $oldName,
                'filePath' => $uploadPath,
                'fileUrl' => $url.'/uploads/'.$folder.'/'.date('Ym').'/'. $hashName,
//                'fileSize'=>$data['size'],
                'fileSuffixes' => $pathInfo['extension'],          //文件后缀名
//                'file_id'=>$data['id'],
            ];
            return json($response);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

}
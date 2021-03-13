<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/6
 * Time: 11:12
 */

namespace sensen\services\upload\storage;


use sensen\basic\BaseUpload;
use sensen\exceptions\UploadException;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Filesystem;
use think\File;
use think\Image;

class Local extends BaseUpload
{
    //默认存放位置
    protected $defaultPath;

    public function initialize(array $config)
    {
        parent::initialize($config);

        $disk = Config::get('filesystem.default');
        $this->defaultPath = Config::get("filesystem.disks.{$disk}.url");
    }

    protected function app()
    {
        // TODO: Implement app() method.
    }

    /**
     * 生成上传文件目录位置
     * @param $path
     * @param null $root
     * @return mixed
     */
    protected function uploadDir($path, $root=null)
    {
        if($root==null){
            $root = app()->getRootPath().'public'.DIRECTORY_SEPARATOR;
        }
        return str_replace('\\', '/', $root.'uploads'.DIRECTORY_SEPARATOR.$path);
    }

    /**
     * 检测上传目录是否存在 不存在则生成
     * @param $dir
     * @return bool
     */
    protected function validDir($dir)
    {
        return is_dir($dir) == true || mkdir($dir, 0777, true) == true;
    }

    /**
     * 上传操作
     * @param string $file
     * @param string $thumb 格式为 axb
     * @return array|bool|mixed
     */
    public function move(string $file = 'file', string $thumb='')
    {
        $fileHandle = app()->request->file($file);
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            try {
                validate([$file => $this->validate])->check([$file => $fileHandle]);
            } catch (ValidateException $e) {
                return $this->setError($e->getMessage());
            }
        }
        $fileName = Filesystem::putFile($this->path, $fileHandle);

        if (!$fileName){
            return $this->setError('Upload failure');
        }
        $filePath = Filesystem::path($fileName);
        $this->fileInfo->uploadInfo = new File($filePath);
        $this->fileInfo->hash = $fileHandle->hash();
        $this->fileInfo->mime = $fileHandle->getMime();
        $this->fileInfo->ext = $fileHandle->extension();
        $this->fileInfo->size = $fileHandle->getSize();
        $this->fileInfo->fileName = $this->fileInfo->uploadInfo->getFilename();
        $this->fileInfo->fileNameOrigin = '';
        $this->fileInfo->filePath = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);

        //是否需要压缩图片
        $this->fileInfo->filePathZip = '';
        if($thumb){
            $thumbSize = explode('x', $thumb);
            $image = Image::open('.'.$this->fileInfo->filePath);
            $thumbPath = str_replace('.'.$this->fileInfo->extension, '', $this->fileInfo->filePath) . '_'.$thumb.'.'.$this->fileInfo->extension;
            $image->thumb($thumbSize[0], $thumbSize[1])->save('.'.$thumbPath);
            $this->fileInfo->filePathZip = $thumbPath;
        }

        return $this->fileInfo;
    }

    /**
     * 文件流上传
     * @param string $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function stream(string $fileContent, string $key = null)
    {
        if (!$key) {
            $key = $this->saveFileName();
        }
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $key;
        file_put_contents($fileName, $fileContent);
        $this->fileInfo->uploadInfo = new File($fileName);
        $this->fileInfo->fileName = $key;
        $this->fileInfo->filePath = '/uploads/' . $this->path . '/' . $key;
        return $this->fileInfo;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool|mixed
     */
    public function delete(string $filePath)
    {
        if (file_exists($filePath)) {
            try {
                unlink($filePath);
                return true;
            } catch (UploadException $e) {
                return $this->setError($e->getMessage());
            }
        }
        return false;
    }

}
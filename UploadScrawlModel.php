<?php
/**
 * File : Upload.php
 * Author : David
 * Date : 2016/01/31
 * Remark : 文件上传模型
 */
namespace diwait\ueditor;

use Yii;

class UploadScrawlModel extends UploadBaseModel {

    public $webRoot;
    public $path;

    public function upload($base64Data)
    {
        $this->getSavePath($this->webRoot, $this->path);
        $base64Data = base64_decode($base64Data);
        $filePath = $this->saveDir . '/' . $this->saveName . '.jpg';
        file_put_contents($filePath, $base64Data);
        if (file_exists($filePath)) {
            return str_replace($this->webRoot, '', $filePath);
        } else {
            return null;
        }
    }
}
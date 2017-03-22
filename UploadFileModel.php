<?php
/**
 * File : Upload.php
 * Author : David
 * Date : 2016/01/31
 * Remark : 文件上传模型
 */
namespace diwait\ueditor;

use Yii;

class UploadFileModel extends UploadBaseModel
{
    public $upfile;
    public $rulesVal;
    public $webRoot;
    public $path;

    public function rules()
    {
        return $this->rulesVal;
    }

    public function upload()
    {
        $this->getSavePath($this->webRoot, $this->path);
        if ($this->validate()) {
            $object = $this->upfile;
            $filePath = $this->saveDir . '/' . $this->saveName . '.' . $object->extension;
            if ($object->saveAs($filePath)) {
                return str_replace($this->webRoot, '', $filePath);
            }
            return null;
        } else {
            return null;
        }
    }

}
<?php
/**
 * File : UploadBaseModel.php
 * Author : David
 * Date : 2017/03/22
 * Remark : Null
 */
namespace diwait\ueditor;

use Yii;
use yii\base\Model;

class UploadBaseModel extends Model
{
    public $saveDir;
    public $saveName;

    public function getSavePath($webRoot, $path)
    {
        $savePath = $webRoot . $path;
        $saveDir = $this->getFullName(dirname($savePath)); // 文件保存目录
        $pathArr = explode('/', $savePath);
        $saveName = $this->getFullName(array_pop($pathArr)); // 文件保存名称

        // 是否存在目录
        if (! file_exists($saveDir)) {
            $this->mkDirs($saveDir);
        }

        $this->saveDir = $saveDir;
        $this->saveName = $saveName;
    }

    /**
     * 重命名文件
     * @return string
     */
    protected function getFullName($path)
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $path;
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{time}", $t, $format);

        //替换随机字符串
        $randNum = rand(1, 10000000000) . rand(1, 10000000000);
        if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
            $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
        }

        return $format;
    }

    /**
     * 递归创建目录
     * @param $dir       目录
     * @param int $mode  权限
     * @return bool
     */
    protected function mkDirs($dir, $mode = 0777)
    {
        if (! is_dir($dir)) {
            if (! self::mkDirs(dirname($dir))) {
                return false;
            }
            if (! mkdir($dir, $mode)) {
                return false;
            }
        }
        return true;
    }
}
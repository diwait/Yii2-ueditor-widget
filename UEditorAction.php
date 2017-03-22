<?php
/**
 * File : UEditorAction.php
 * Author : David
 * Date : 2017/03/18
 * Remark : Null
 */
namespace diwait\ueditor;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;
use yii\web\Response;
use yii\web\UploadedFile;

class UEditorAction extends Action
{
    public $config = [];
    private $filepath;
    private $stateInfo; //上传状态信息,
    private $stateMap = [ //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE"           => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED"        => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED"   => "文件类型不允许",
        "ERROR_CREATE_DIR"         => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE"  => "目录没有写权限",
        "ERROR_FILE_MOVE"          => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND"     => "找不到上传文件",
        "ERROR_WRITE_CONTENT"      => "写入文件内容错误",
        "ERROR_UNKNOWN"            => "未知错误",
        "ERROR_DEAD_LINK"          => "链接不可用",
        "ERROR_HTTP_LINK"          => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE"   => "链接contentType不正确"
    ];

    /**
     * 文件保存规则参数如下:
     * imagePathFormat
     * filePathFormat
     * videoPathFormat
     * scrawlPathFormat
     */
    public function init()
    {
        // close csrf
        Yii::$app->request->enableCsrfValidation = false;
        // 默认设置
        $_config = require(__DIR__ . '/config.php');

        // 添加图片默认root路径与访问网址
        $this->config['imageRoot'] = isset($this->config['imageRoot']) ? $this->config['imageRoot'] : Yii::getAlias('@webroot');
        $this->config['imageUrl'] = isset($this->config['imageUrl']) ? $this->config['imageUrl'] : $this->nowUrl();
        $this->config['scrawlRoot'] = isset($this->config['scrawlRoot']) ? $this->config['scrawlRoot'] : Yii::getAlias('@webroot');
        $this->config['scrawlUrl'] = isset($this->config['scrawlUrl']) ? $this->config['scrawlUrl'] : $this->nowUrl();
        $this->config['videoRoot'] = isset($this->config['videoRoot']) ? $this->config['videoRoot'] : Yii::getAlias('@webroot');
        $this->config['videoUrl'] = isset($this->config['videoUrl']) ? $this->config['videoUrl'] : $this->nowUrl();
        $this->config['fileRoot'] = isset($this->config['fileRoot']) ? $this->config['fileRoot'] : Yii::getAlias('@webroot');
        $this->config['fileUrl'] = isset($this->config['fileUrl']) ? $this->config['fileUrl'] : $this->nowUrl();

        $this->config = ArrayHelper::merge($_config, $this->config);
        parent::init();
    }

    public function run()
    {
        if (Yii::$app->request->get('callback', false)) {
            Yii::$app->response->format = Response::FORMAT_JSONP;
        } else {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        $result = '';
        switch (Yii::$app->request->get('action')) {
            // 获取后端配置
            case 'config' :
                $result = $this->config;
                break;
            // 上传图片
            case 'uploadimage':
            // 上传涂鸦
            case 'uploadscrawl':
            // 上传视频
            case 'uploadvideo':
            // 上传文件
            case 'uploadfile':
                $result = $this->actionUpload();
                break;
            // 获取文件列表
            case 'listfile' :
                $allowFiles =  $this->config['fileManagerAllowFiles'];
                $listSize =  $this->config['fileManagerListSize'];
                $path =  $this->config['fileManagerListPath'];
                $result = $this->getLists($allowFiles, $listSize, $path, $this->config['fileRoot'], $this->config['fileUrl']);
                break;
            // 获取图片列表
            case 'listimage' :
                $allowFiles =  $this->config['imageManagerAllowFiles'];
                $listSize =  $this->config['imageManagerListSize'];
                $path =  $this->config['imageManagerListPath'];
                $result = $this->getLists($allowFiles, $listSize, $path, $this->config['imageRoot'], $this->config['imageUrl']);
                break;
        }
        return $result;
    }

    /**
     * 上传处理
     * @return bool|string
     */
    protected function actionUpload()
    {
        $status = false;
        switch (Yii::$app->request->get('action')) {
            case 'uploadimage' :
                $status = $this->uploadImage();
                break;
            case 'uploadscrawl' :
                $status = $this->uploadScrawl();
                break;
            case 'uploadfile' :
                $status = $this->uploadFile();
                break;
            case 'uploadvideo' :
                $status = $this->uploadVideo();
                break;
        }
        return $status ? $this->getFileInfo() : $status;
    }

    /**
     * 上传图片
     * @return string
     */
    protected function uploadImage()
    {
        $model = new \diwait\ueditor\UploadImageModel();
        $model->webRoot = $this->config['imageRoot'];
        $model->path = $this->config['imagePathFormat'];
        // 上传文件字段名称
        $FullfieldName = $this->config['imageFieldName'];
        $fieldName = str_replace([BaseHtml::getAttributeName($FullfieldName), '[', ']'], [''], $FullfieldName);
        $model->rulesVal = [
            [$fieldName, 'file', 'skipOnEmpty' => false, 'extensions' => $this->dealExtensions($this->config['imageAllowFiles']), 'uploadRequired' => '请上传文件', 'wrongExtension' => '请上传格式为' . $this->dealExtensions($this->config['imageAllowFiles']) . '的图片']
        ];

        if (Yii::$app->request->isPost) {
            $model->$fieldName = UploadedFile::getInstance($model, $fieldName);
            if ($result = $model->upload()) {
                if ($result !== null) {
                    $this->stateInfo = $this->stateMap[0];
                    $this->filepath = $this->config['imageUrl'] . $result;
                    return true;
                }
                return false;
            } else {
                $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
                return false;
            }
        } else {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return false;
        }
    }

    /**
     * 上传涂鸦
     * @return bool
     */
    protected function uploadScrawl()
    {
        if ($base64Data = Yii::$app->request->post('imgFile')) {
            $model = new UploadScrawlModel();
            $model->webRoot = $this->config['scrawlRoot'];
            $model->path = $this->config['scrawlPathFormat'];
            $result = $model->upload($base64Data);

            if ($result !== null && file_exists($this->config['scrawlRoot'] . $result)) {
                $this->stateInfo = $this->stateMap[0];
                $this->filepath = $this->config['scrawlUrl'] . $result;
                return true;
            } else {
                $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
                return false;
            }
        } else {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
            return false;
        }
    }

    /**
     * 上传附件
     * @return string
     */
    protected function uploadFile()
    {
        $model = new \diwait\ueditor\UploadFileModel();
        $model->webRoot = $this->config['fileRoot'];
        $model->path = $this->config['filePathFormat'];

        // 上传文件字段名称
        $FullfieldName = $this->config['fileFieldName'];
        $fieldName = str_replace([BaseHtml::getAttributeName($FullfieldName), '[', ']'], [''], $FullfieldName);
        $model->rulesVal = [
            [$fieldName, 'file', 'skipOnEmpty' => false, 'extensions' => $this->dealExtensions($this->config['fileAllowFiles']), 'uploadRequired' => '请上传文件', 'wrongExtension' => '请上传格式为' . $this->dealExtensions($this->config['fileAllowFiles']) . '的文件', 'checkExtensionByMimeType' => false]
        ];

        if (Yii::$app->request->isPost) {
            $model->$fieldName = UploadedFile::getInstance($model, $fieldName);
            if ($result = $model->upload()) {
                if ($result !== null) {
                    $this->stateInfo = $this->stateMap[0];
                    $this->filepath = $this->config['fileUrl'] . $result;
                    return true;
                }
                return false;
            } else {
                $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
                return false;
            }
        } else {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return false;
        }
    }

    /**
     * 上传视频
     * @return string
     */
    protected function uploadVideo()
    {
        $model = new \diwait\ueditor\UploadVideoModel();
        $model->webRoot = $this->config['videoRoot'];
        $model->path = $this->config['videoPathFormat'];

        // 上传文件字段名称
        $FullfieldName = $this->config['videoFieldName'];
        $fieldName = str_replace([BaseHtml::getAttributeName($FullfieldName), '[', ']'], [''], $FullfieldName);
        $model->rulesVal = [
            [$fieldName, 'file', 'skipOnEmpty' => false, 'extensions' => $this->dealExtensions($this->config['videoAllowFiles']), 'uploadRequired' => '请上传文件', 'wrongExtension' => '请上传格式为' . $this->dealExtensions($this->config['videoAllowFiles']) . '的视频', 'checkExtensionByMimeType' => false]
        ];

        if (Yii::$app->request->isPost) {
            $model->$fieldName = UploadedFile::getInstance($model, $fieldName);
            if ($result = $model->upload()) {
                if ($result !== null) {
                    $this->stateInfo = $this->stateMap[0];
                    $this->filepath = $this->config['videoUrl'] . $result;
                    return true;
                }
                return false;
            } else {
                $this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
                return false;
            }
        } else {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return false;
        }
    }

    /**
     * 获取文件列表
     * @param $allowFiles
     * @param $listSize
     * @param $path
     * @param $webRoot
     * @return string
     */
    protected function getLists($allowFiles, $listSize, $path, $webRoot, $webUrl)
    {
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        // 获取参数
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        // 获取文件列表
        $path = $webRoot . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $webRoot, $webUrl, $allowFiles);
        if (! count($files)) {
            return [
                "state" => "no match file",
                "list" => [],
                "start" => $start,
                "total" => count($files)
            ];
        }

        // 获取指定范围的列表
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = []; $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        // 返回数据
        return [
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ];
    }

    /**
     * 处理扩展名前的点符号
     * @param $extensions
     * @return mixed
     */
    protected function dealExtensions($extensions)
    {
        return str_replace('.', '', implode(',', $extensions));
    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    protected function getStateInfo($errCode)
    {
        return ! $this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取当前域名
     * @return mixed
     */
    protected function nowUrl()
    {
        return $this->isHttps() ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
    }

    /**
     * 判断是否Https
     * @return bool
     */
    protected function isHttps()
    {
        if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }

        return false;
    }

    /**
     * 返回文件上传信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "state"    => $this->stateInfo,
            "url"      => $this->filepath
        );
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    protected function getfiles($path, $webRoot, $webUrl, $allowFiles, &$files = [])
    {
        if (! is_dir($path)) { return null; };
        if (substr($path, strlen($path) - 1) != '/') { $path .= '/'; }
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    self::getfiles($path2, $webRoot, $webUrl, $allowFiles, $files);
                } else {
                    if (preg_match("/\\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = [
                            'url'=> $webUrl . substr($path2, strlen($webRoot)),
                            'mtime'=> filemtime($path2)
                        ];
                    }
                }
            }
        }
        return $files;
    }
}
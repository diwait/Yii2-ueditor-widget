<?php
/**
 * File : UEditor.php
 * Author : David
 * Date : 2017/03/18
 * Remark : Null
 */
namespace diwait\ueditor;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\InputWidget;

class UEditor extends InputWidget
{
    public $ueditorOptions;

    public $_options;

    protected $ueditorID;

    public function init()
    {
        if (isset($this->options['id'])) {
            $this->id = $this->options['id'];
        } else {
            $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        }
        $this->ueditorID = $this->id . '-editor';
        $this->_options = [
            'serverUrl' => Url::to(['upload']),
        ];
        $this->ueditorOptions = ArrayHelper::merge($this->_options, $this->ueditorOptions);
        parent::init();
    }

    public function run()
    {
        $this->registerUEditorScript();
        if ($this->hasModel()) {
            $scriptCode = '<script id="' . $this->ueditorID . '" name="' . BaseHtml::getInputName($this->model, $this->attribute) . '" type="text/plain"></script>';
            return  $scriptCode . Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id, 'style' => 'display:none']);
        } else {
            $scriptCode = '<script id="' . $this->ueditorID . '" name="' . $this->value . '" type="text/plain"></script>';
            return $scriptCode . Html::textarea($this->id, $this->value, ['id' => $this->id, 'style' => 'display:none']);
        }
        parent::run();
    }

    protected function registerUEditorScript()
    {
        UEditorAsset::register($this->view);
        if ($this->ueditorOptions) {
            $ueditorOptions = Json::encode($this->ueditorOptions);
        } else {
            $ueditorOptions = Json::encode([]);
        }
        $script = "UE.getEditor('" . $this->ueditorID . "', " . $ueditorOptions . ");";
        $this->view->registerJs($script, View::POS_READY);
    }
}
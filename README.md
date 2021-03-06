Yii2-ueditor-widget
===================================

安装方法
-----------------------------------
```
composer require diwait/ueditor:"*"
```

使用方法
-----------------------------------
第一,在view中加入widget
```php
$form->field($model, 'content')->widget('diwait\ueditor\UEditor', [
    'ueditorOptions' => [ // ueditorOptions 为百度编辑器的前端选项配置, 如里面有"toolbars"、"autoHeightEnabled"等
        'toolbars' => [
            [
                'source', //源代码
                'undo', //撤销
                'redo', //重做
                'fontfamily', //字体
                'fontsize', //字号
                'paragraph', //段落格式
                'bold', //加粗
                'italic', //斜体
                'underline', //下划线
                'strikethrough', //删除线
                'subscript', //下标
                'indent', //首行缩进
                'justifyleft', //居左对齐
                'justifyright', //居右对齐
                'justifycenter', //居中对齐
                'justifyjustify', //两端对齐
                'directionalityltr', //从左向右输入
                'directionalityrtl', //从右向左输入
                'rowspacingtop', //段前距
                'rowspacingbottom', //段后距
                'customstyle', //自定义标题
                'autotypeset', //自动排版
                'touppercase', //字母大写
                'tolowercase', //字母小写
                'forecolor', //字体颜色
                'backcolor', //背景色
                'insertorderedlist', //有序列表
                'insertunorderedlist', //无序列表
                'emotion', //表情
                'spechars', //特殊字符
                'fontborder', //字符边框
                'superscript', //上标
                'formatmatch', //格式刷
                'blockquote', //引用
                'pasteplain', //纯文本粘贴模式
                'selectall', //全选
                'print', //打印
                'preview', //预览
                'horizontal', //分隔线
                'removeformat', //清除格式
                'time', //时间
                'date', //日期
                'anchor', //锚点
                'insertrow', //前插入行
                'insertcol', //前插入列
                'mergeright', //右合并单元格
                'mergedown', //下合并单元格
                'deleterow', //删除行
                'deletecol', //删除列
                'splittorows', //拆分成行
                'splittocols', //拆分成列
                'splittocells', //完全拆分单元格
                'deletecaption', //删除表格标题
                'inserttitle', //插入标题
                'mergecells', //合并多个单元格
                'deletetable', //删除表格
                'cleardoc', //清空文档
                'insertparagraphbeforetable', //"表格前插入行"
                'insertcode', //代码语言
                'simpleupload', //单图上传
                'insertimage', //多图上传
                'edittable', //表格属性
                'edittd', //单元格属性
                'unlink', //取消链接
                'link', //超链接
                'searchreplace', //查询替换
                'map', //Baidu地图
                'gmap', //Google地图
                'insertvideo', //视频
                'help', //帮助
                'fullscreen', //全屏
                'pagebreak', //分页
                'insertframe', //插入Iframe
                'imagenone', //默认
                'imageleft', //左浮动
                'imageright', //右浮动
                'attachment', //附件
                'imagecenter', //居中
                'wordimage', //图片转存
                'lineheight', //行间距
                'edittip ', //编辑提示
                'background', //背景
                'template', //模板
                'scrawl', //涂鸦
                'music', //音乐
                'inserttable', //插入表格
                'drafts', // 从草稿箱加载
                'charts', // 图表
            ]
        ]
    ];
```
百度Ueditor前端参数请看 http://fex.baidu.com/ueditor/#start-config

第二,在controller中的actions中加入
```php
'upload' => [
    'class' => 'diwait\ueditor\UEditorAction'
    'config' => [
        // 图片保存的根目录(不要以/结尾)
        'imageRoot' => Yii::getAlias('@frontend') . '/web',
        // 图片访问的网址(不要以/结尾)
        'imageUrl' => 'http://www.xxx.com',
        // 图片保存与命名规则(不要以/结尾), 最后一段为图片的命名规则
        'imagePathFormat' => '/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}',

        /* 凡涉及到文件保存的规则, 请参加以下 */
        /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
        /* {time} 会替换成时间戳 */
        /* {yyyy} 会替换成四位年份 */
        /* {yy} 会替换成两位年份 */
        /* {mm} 会替换成两位月份 */
        /* {dd} 会替换成两位日期 */
        /* {hh} 会替换成两位小时 */
        /* {ii} 会替换成两位分钟 */
        /* {ss} 会替换成两位秒 */
        /* 非法字符 \ => * ? " < > | */

        // 涂鸦保存的根目录(不要以/结尾)
        'scrawlRoot' => Yii::getAlias('@frontend') . '/web',
        // 涂鸦访问的网址(不要以/结尾)
        'scrawlUrl' => 'http://www.xxx.com',
        // 涂鸦保存与命名规则(不要以/结尾), 最后一段为图片的命名规则
        'scrawlPathFormat' => '/upload/scrawl/{yyyy}{mm}{dd}/{time}{rand:6}',

        // 媒体资源保存的根目录(不要以/结尾)
        'videoRoot' => Yii::getAlias('@frontend') . '/web',
        // 媒体资源访问的网址(不要以/结尾)
        'videoUrl' => 'http://www.xxx.com',
        // 媒体资源保存与命名规则(不要以/结尾), 最后一段为图片的命名规则
        'videoPathFormat' => '/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}',

        // 附件保存的根目录(不要以/结尾)
        'fileRoot' => Yii::getAlias('@frontend') . '/web',
        // 附件访问的网址(不要以/结尾)
        'fileUrl' => 'http://www.xxx.com',
        // 附件保存与命名规则(不要以/结尾), 最后一段为图片的命名规则
        'filePathFormat' => '/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}'
    ]
]
```

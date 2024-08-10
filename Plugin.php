<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 基于SweetAlert2的图片放大效果
 *
 * @package ExImg
 * @author fmujie
 * @version 1.0.1
 * @link https://blog.fmujie.cn
 */
class ExImg_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        //imagesExpand开关
        $imgOptions = [
            'default' => _t('关闭'),
            'moderate_enlarged' => _t('较小'),
            'larger_enlarged' => _t('适中'),
            'largest_enlarged' => _t('较大'),
        ];
        $expandImgType = new Typecho_Widget_Helper_Form_Element_Radio('expandImgType', $imgOptions, 'default', _t('图片放大效果, 默认关闭'));
        $form->addInput($expandImgType);

        $clickOptions = [
            "dbclick" => _t('双击'),
            "click" => _t('单击')
        ];
        $clickType = new Typecho_Widget_Helper_Form_Element_Radio('clickType', $clickOptions, 'click', _t('触发条件: 默认单击'));
        $form->addInput($clickType);

        //imagesExpandBg选择
        $imgBgOptions = [
            'rgba(0,0,123,0.4)' => _t('幻影紫'),
            'rgba(160,238,225,0.4)' => _t('纯净绿'),
            'rgba(236,173,158,0.4)' => _t('暖心红'),
            'rgba(145,196,255,0.4)' => _t('天空蓝'),
        ];
        $expandImgBgType = new Typecho_Widget_Helper_Form_Element_Radio('expandImgBgType', $imgBgOptions, 'rgba(0,0,123,0.4)', _t('图片放大后的背景颜色, 默认幻影紫(启用图片放大后生效)'));
        $form->addInput($expandImgBgType);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {}

    /**
     *为header添加css文件
     * @return void
     */
    public static function header()
    {
        $expandImgType = Typecho_Widget::widget('Widget_Options')->plugin('ExImg')->expandImgType;
        if ($expandImgType != 'default') {
            $StaticCssUrl = Helper::options()->pluginUrl . '/ExImg/static/css/';
            echo '<link rel="stylesheet" href=" ' . $StaticCssUrl . 'style.css"/>';
            echo '<script type="text/javascript" src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js"></script>';
            echo '<script src="https://cdn.bootcss.com/limonte-sweetalert2/7.33.1/sweetalert2.all.js"></script>';
        }
    }

    /**
     *为footer添加js文件
     * @return void
     */
    public static function footer()
    {
        $expandImgType = Typecho_Widget::widget('Widget_Options')->plugin('ExImg')->expandImgType;
        $expandImgBgType = Typecho_Widget::widget('Widget_Options')->plugin('ExImg')->expandImgBgType;
        $clickType = Typecho_Widget::widget('Widget_Options')->plugin('ExImg')->clickType;
        if ($expandImgType != 'default') {
            self::handleImgExType($expandImgType, $expandImgBgType, $clickType);
        }
    }

    /*imgExpandType*/
    private static function handleImgExType($expandImgType, $expandImgBgType, $clickType)
    {
        switch ($expandImgType) {
            case 'moderate_enlarged':
                $imgWidth = 800;
                break;
            case 'larger_enlarged':
                $imgWidth = 1000;
                break;
            case 'largest_enlarged':
                $imgWidth = 1200;
                break;
            default:
                $imgWidth = 800;
                break;
        }
        $js .= '<script>';
        $js .= <<<JS
        $(document).ready(function () {
            var eventType = "{$clickType}";
            var showImage = function(e) {
                var element = $(e.target);
                var tagName = element.prop('tagName');
                if (tagName == 'IMG') {
                    var imgSrc = element.attr('src');
                    swal({
                        width: {$imgWidth},
                        padding: 20,
                        imageUrl: imgSrc,
                        imageClass: '{$expandImgType}',
                        backdrop: '{$expandImgBgType}',
                        showConfirmButton: false,
                        showCloseButton: true,
                    });
                }
            };

            if (eventType == 'click') {
                $(document).click(showImage);
            } else {
                $(document).dblclick(showImage);
            }
        });

JS;
        $js .= '</script>';
        echo $js;
    }
}

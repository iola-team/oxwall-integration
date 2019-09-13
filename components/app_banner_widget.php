<?php

class IOLA_CMP_AppBannerWidget extends BASE_CLASS_Widget
{
    public function __construct(BASE_CLASS_WidgetParameter $paramObj)
    {
        parent::__construct();

        $settings = $paramObj->customParamList;

        $this->assign("settings", array_merge($settings, [
            "text" => str_replace('"', "'", OW::getLanguage()->text("iola", "banner_widget_text", [
                "moreUrl" => "https://iola.app/for-users"
            ]))
        ]));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        IOLA_CLASS_Plugin::getInstance()->addStatic();
    }

    public static function getSettingList()
    {
        $settingList = [];

        $settingList['showLogo'] = [
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('iola', 'banner_widget_show_logo'),
            'value' => 3
        ];

        $settingList['showText'] = [
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('iola', 'banner_widget_show_text'),
            'value' => 3
        ];

        $settingList['showIOS'] = [
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('iola', 'banner_widget_show_ios'),
            'value' => 3
        ];

        $settingList['showAndroid'] = [
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('iola', 'banner_widget_show_android'),
            'value' => 3
        ];

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return [
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('iola', 'banner_widget_title'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_MOBILE
        ];
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}
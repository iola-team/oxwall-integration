<?php

class IOLA_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function init()
    {
        $plugin = OW::getPluginManager()->getPlugin("iola");
        $build = $plugin->getDto()->build;
        $staticUrl = $plugin->getStaticUrl();

        OW::getDocument()->addScript($staticUrl . "vendor.js?" . $build);
        OW::getDocument()->addScript($staticUrl . "iola.js?" . $build);
        OW::getDocument()->addStyleSheet($staticUrl . "iola.css?" . $build);
    }

    public function index()
    {
        $language = OW::getLanguage();
        
        $this->setPageTitle($language->text("iola", "settings_page_title"));
        $this->setPageHeading($language->text("iola", "settings_page_heading"));

        $ids = [
            "container" => uniqid(),
            "logoInput" => uniqid(),
            "backgroundInput" => uniqid(),
            "primaryColorInput" => uniqid(),
            "primaryColorPicker" => uniqid(),
            "preview" => uniqid(),
            "saveButton" => uniqid()
        ];

        $this->assign("ids", $ids);

        $options = [
            "ids" => $ids
        ];

        $js = UTIL_JsGenerator::newInstance()->callFunction(
            ["IOLA", "pages", "Settings", "init"],
            [$options]
        );

        OW::getDocument()->addOnloadScript($js);
    }
}
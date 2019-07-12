<?php

class IOLA_CTRL_Admin extends OW_ActionController
{
    public function init()
    {
        $plugin = OW::getPluginManager()->getPlugin("iola");
        $build = $plugin->getDto()->build;
        $staticUrl = $plugin->getStaticUrl();

        OW::getDocument()->addScript($staticUrl . "iola.js?" . $build);
        OW::getDocument()->addStyleSheet($staticUrl . "iola.css?" . $build);
    }

    public function index()
    {
        $language = OW::getLanguage();
        
        $this->setPageTitle($language->text("iola", "settings_page_title"));
        $this->setPageHeading($language->text("iola", "settings_page_heading"));
    }
}
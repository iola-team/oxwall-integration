<?php

class IOLA_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @var IOLA_BOL_Service
     */
    protected $service;

    /**
     * @var IOLA_CLASS_Plugin
     */
    protected $plugin;

    public function __construct()
    {
        parent::__construct();

        $this->service = IOLA_BOL_Service::getInstance();
        $this->plugin = IOLA_CLASS_Plugin::getInstance();
    }

    public function init()
    {
        $this->plugin->addStatic();
    }

    public function index()
    {
        $language = OW::getLanguage();
        $staticDir = OW::getPluginManager()->getPlugin("iola")->getStaticUrl();
        
        $this->setPageTitle($language->text("iola", "settings_page_title"));
        $this->setPageHeading($language->text("iola", "settings_page_heading"));

        if (!$this->plugin->isReady()) {
            $requirements = $this->plugin->getRequirements();
            $this->assign("content", $language->text("iola", "admin_requirements_page_text", [
                "friendsUrl" => $requirements["friends"],
                "mailboxUrl" => $requirements["mailbox"],
                "photoUrl" => $requirements["photo"]
            ]));

            $this->setTemplate(
                $this->plugin->getPlugin()->getCtrlViewDir() . "admin_requirements.html"
            );

            return;
        }

        $configs = $this->service->getConfigs();
        $uniqId = uniqid('iola-');

        $backgroundUrl = $this->service->getFileUrl("backgroundUrl");
        $logoUrl = $this->service->getFileUrl("logoUrl");

        $values = [
            "backgroundUrl" => empty($backgroundUrl) 
                ? $staticDir . "default-background.jpg"
                : $backgroundUrl,

            "logoUrl" => empty($logoUrl) 
                ? $staticDir . "default-logo.png"
                : $logoUrl,

            "primaryColor" => empty($configs["primaryColor"]) ? "#5259FF" : $configs["primaryColor"]
        ];

        $this->assign("values", $values);
        $this->assign("uniqId", $uniqId);

        $options = [
            "uniqId" => $uniqId,
            "values" => $values,
            "rsp" => [
                "save" => OW::getRouter()->urlForRoute("iola.admin-settings-save")
            ]
        ];

        $js = UTIL_JsGenerator::newInstance()->callFunction(
            ["IOLA", "pages", "Settings", "init"],
            [$options]
        );

        OW::getDocument()->addOnloadScript($js);
    }

    public function saveSettings()
    {
        if (!OW::getRequest()->isPost() || !OW::getUser()->isAdmin()) {
            throw new Redirect403Exception();
        }

        $language = OW::getLanguage();
        $error = false;
        
        try {
            if (isset($_FILES["background"])) {
                $this->service->saveFile(
                    "backgroundUrl",
                    $this->service->uploadImage($_FILES["background"])
                );
            }
    
            if (isset($_FILES["logo"])) {
                $this->service->saveFile(
                    "logoUrl",
                    $this->service->uploadImage($_FILES["logo"])
                );
            }
        } catch (InvalidArgumentException $exception) {
            $error = true;
        }
        
        if (isset($_POST["primaryColor"])) {
            $colorPattern = "/#([a-fA-F0-9]{3}){1,2}\b/";

            if (preg_match($colorPattern, $_POST["primaryColor"])) {
                $this->service->saveConfigs([
                    "primaryColor" => $_POST["primaryColor"]
                ]);
            } else {
                $error = true;
            }
        }

        echo json_encode([
            "info" => $error ? null : $language->text("iola", "settings_save_success"),
            "error" => $error ? $language->text("iola", "settings_save_error") : null
        ]);

        exit;
    }
}
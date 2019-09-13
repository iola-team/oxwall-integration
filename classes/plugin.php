<?php

use Iola\Oxwall\Server;
use Iola\Oxwall\ServerController;

/**
 * @method static IOLA_CLASS_Plugin getInstance()
 */
class IOLA_CLASS_Plugin
{
    use OW_Singleton;

    /**
     * @var OW_Plugin
     */
    protected $plugin;

    protected $requiredPlugins = [
        "photo" => "https://developers.oxwall.com/store/item/16",
        "friends" => "https://developers.oxwall.com/store/item/14",
        "mailbox" => "https://developers.oxwall.com/store/item/10",
    ];

    protected function __construct()
    {
        $this->plugin = OW::getPluginManager()->getPlugin("iola");
    }

    /**
     * @return OW_Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getRequirements()
    {
        return $this->requiredPlugins;
    }

    public function isReady()
    {
        $isReady = true;
        foreach (array_keys($this->requiredPlugins) as $pluginKey) {
            if (!OW::getPluginManager()->isPluginActive($pluginKey)) {
                $isReady = false;

                continue;
            }
        }

        return $isReady;
    }

    public function addStatic()
    {
        $build = $this->getPlugin()->getDto()->build;
        $staticUrl = $this->getPlugin()->getStaticUrl();
        $gz = OW_DEV_MODE ? "" : ".gz";

        OW::getDocument()->addScript($staticUrl . "vendor.js{$gz}?" . $build);
        OW::getDocument()->addScript($staticUrl . "iola.js{$gz}?" . $build);
        OW::getDocument()->addStyleSheet($staticUrl . "iola.css{$gz}?" . $build);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     * 
     * @return void
     */
    protected function onCollectAdminNotifications($event)
    {
        $language = OW::getLanguage();

        $event->add($language->text("iola", "admin_requirements_notification", [
            "photoUrl" => $this->requiredPlugins["photo"],
            "friendsUrl" => $this->requiredPlugins["friends"],
            "mailboxUrl" => $this->requiredPlugins["mailbox"],
            "settingsUrl" => OW::getRouter()->urlForRoute("iola.admin-settings")
        ]));
    }

    protected function limitedInit()
    {
        OW::getEventManager()->bind("admin.add_admin_notification", function($event) {
            $this->onCollectAdminNotifications($event);
        });
    }

    public function init()
    {
        /**
         * Run limited initialization if some of the plugin requirements are not met
         */
        if (!$this->isReady()) {
            return $this->limitedInit();
        }

        /**
         * Redirect exceptions
         */
        $exceptionsKeys = [
            "base.members_only",
            "base.splash_screen",
            "base.password_protected",
            "base.maintenance_mode",
            "base.wait_for_approval",
            "base.suspended_user",
            "base.email_verify",
            "base.complete_profile",
            "base.complete_profile.account_type"
        ];

        foreach ($exceptionsKeys as $exceptionKey) {
            OW::getRequestHandler()->addCatchAllRequestsExclude($exceptionKey, ServerController::class);
        }

        /**
         * Init Application
         */
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, function() {

            /**
             * Init iola integration
             */
            Server::getInstance()->init();
        });
    }
}
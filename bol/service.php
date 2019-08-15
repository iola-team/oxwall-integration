<?php

/**
 * @method static IOLA_CLASS_Plugin getInstance()
 */
class IOLA_BOL_Service
{
    use OW_Singleton;

    /**
     * @var OW_Plugin
     */
    protected $plugin;

    protected function __construct()
    {
        $this->plugin = OW::getPluginManager()->getPlugin("iola");
    }

    public function saveConfigs($configs)
    {
        $config = OW::getConfig();

        foreach ($configs as $name => $value) {
            if ($config->configExists("iola", $name)) {
                $config->saveConfig("iola", $name, $value);
            } else {
                $config->addConfig("iola", $name, $value);
            }
        }
    }

    public function getConfigs()
    {
        return OW::getConfig()->getValues("iola");
    }

    public function uploadImage(array $file)
    {
        if (!UTIL_File::validateImage($file["name"])) {
            throw new InvalidArgumentException("Invalid image", 1);
        }

        return $this->uploadFile($file);
    }

    public function uploadFile(array $file)
    {
        $uploadDir = $this->plugin->getPluginFilesDir();
        $fileName = uniqid('upload-') . "." . UTIL_File::getExtension($file["name"]);
        $path = $uploadDir . $fileName;

        if (move_uploaded_file($file["tmp_name"], $path)) {
            return $path;
        }

        return null;
    }

    public function saveFile($configName, $filePath)
    {
        $configs = $this->getConfigs();
        $saveDir = $this->plugin->getUserFilesDir();
        $fileName = uniqid($configName . "-") . "." . UTIL_File::getExtension($filePath);
        $newFilePath = $saveDir . $fileName;

        if (OW::getStorage()->copyFile($filePath, $newFilePath)) {
            if (!empty($configs[$configName])) {
                OW::getStorage()->removeFile($saveDir . $configs[$configName]);
            }

            $this->saveConfigs([
                $configName => $fileName
            ]);

            return true;
        }

        return false;
    }

    public function getFileUrl($configName)
    {
        $configs = $this->getConfigs();
        $dirUrl = $this->plugin->getUserFilesDir();

        return empty($configs[$configName])
            ? null
            : OW::getStorage()->getFileUrl($dirUrl . $configs[$configName]);
    }

    public function getFilePath($configName)
    {
        $configs = $this->getConfigs();
        $dirPath = $this->plugin->getUserFilesDir();

        return empty($configs[$configName]) ? null : $dirPath . $configs[$configName];
    }
}
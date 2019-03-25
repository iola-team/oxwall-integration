<?php

namespace Everywhere\Oxwall;

class ExtensionManager
{
    /**
     * @var \OW_EventManager
     */
    protected $eventManager;
    protected $extensionMap = [];

    public function __construct(array $extensionMap = [])
    {
        $this->eventManager = \OW::getEventManager();
        $this->extensionMap = $extensionMap;
    }

    /**
     * @param $className
     * @param $arguments
     * @return null|object
     *
     * @throws \ReflectionException
     */
    protected function createExtendedInstance($className, $arguments)
    {
        if (!isset($this->extensionMap[$className])) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($this->extensionMap[$className]);

        return $reflectionClass->newInstanceArgs($arguments);
    }

    public function init()
    {
        $this->eventManager->bind("class.get_instance", function(\OW_Event $event) {
            $params = $event->getParams();

            $instance = $this->createExtendedInstance($params["className"], $params["arguments"]);
            if ($instance) {
                $event->setData($instance);
            }
        });
    }
}

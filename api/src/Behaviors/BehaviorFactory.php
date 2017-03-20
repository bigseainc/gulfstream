<?php

namespace BigSea\Gulfstream\API\Behaviors;

class BehaviorFactory
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __call($method, $args)
    {
        $behavior = $this->generate($method);
        if (!$behavior) {
            throw new \Exception("Behavior method '{$method}' not found");
        }
        return call_user_func_array($behavior, $args);
    }

    private function generate($method)
    {
        $class = '\\BigSea\\Gulfstream\\Behaviors\\'. ucfirst($method) . 'Behavior';
        if (!class_exists($class)) {
            $this->container->logger->warning('Call to nonexistant core method: '.$method);
            return null;
        }
        return new $class($this->container);
    }
}

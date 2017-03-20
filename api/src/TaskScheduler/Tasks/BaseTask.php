<?php

namespace BigSea\Gulfstream\API\TaskScheduler\Tasks;

use Slim\Container;

abstract class BaseTask
{
    protected $container;
    protected $scheduler;

    public function __construct(Container $container, $taskScheduler)
    {
        $this->scheduler = $taskScheduler;
        $this->container = $container;
    }

    abstract public function __invoke($params);

    public function rescheduleTime()
    {
        return time() + 3600; // try again in an hour by default
    }
}

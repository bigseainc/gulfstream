<?php

namespace BigSea\Gulfstream\API\TaskScheduler\Tasks;

use BigSea\Gulfstream\API\TaskScheduler;

class DebugTask extends BaseTask
{
    public function __invoke($args)
    {
        var_dump($args);
        return TaskScheduler::TASK_COMPLETE;
    }
}

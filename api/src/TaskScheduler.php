<?php

namespace BigSea\Gulfstream\API;

/**
 * @SuppressWarnings(PHPMD)
 */
class TaskScheduler
{
    const TASK_COMPLETE = 0; // finish this
    const TASK_SKIPPED = 1; // requeue this
    const TASK_IMPOSSIBLE = 2; // log this and quit
    const TASK_RESCHEDULE = 3; // task has specific reschedule time, ask for it
    const TASK_DISABLE = 4; // We screwed up, but we'd rather this task get disabled than removed

    private $container;
    private $storage;

    public function __construct($container, TaskScheduler\StorageInterface $storage)
    {
        $this->container = $container;
        $this->storage = $storage;
    }

    public function process($timeoutInSeconds = 0)
    {
        $limit = !!$timeoutInSeconds; // truthy value = do limit time
        $endtime = time() + $timeoutInSeconds;
        // extra 30 seconds, grab a chunk, just in case, of things added during this flow.
        $tasks = $this->storage->iteratorBefore(time()+30);
        //$this->logInfo('Processing task queue.');
        $taskCounter = [0,0,0,0];
        $weHadATask = false;
        foreach ($tasks as $task) {
            $weHadATask = true;
            if ($limit && $endtime < time()) {
                $this->storage->release($task->id); // ensure we've released the row
                break;
            }

            $cname = $task->class;
            if (!class_exists($cname)) {
                $this->logWarning($task, 'Task class did not exist');
                $taskCounter[self::TASK_IMPOSSIBLE]++;
                $this->storage->disable($task->id);
                continue;
            }

            try {
                $obj = new $cname($this->container, $this);
                $status = $obj(unserialize($task->params));
                switch ($status) {
                    case self::TASK_COMPLETE:
                        $taskCounter[self::TASK_COMPLETE]++;
                        $this->logTaskInfo($task, "Task completed ({$cname})");
                        $this->storage->finish($task->id);
                        break;
                    case self::TASK_SKIPPED:
                        $taskCounter[self::TASK_SKIPPED]++;
                        $this->logTaskInfo($task, "Task skipped ({$cname})");
                        $this->storage->requeue($task->id);
                        break;
                    case self::TASK_IMPOSSIBLE:
                        $taskCounter[self::TASK_IMPOSSIBLE]++;
                        $this->logWarning($task, "Impossible task ({$cname})");
                        $this->storage->finish($task->id);
                        break;
                    case self::TASK_RESCHEDULE:
                        $taskCounter[self::TASK_RESCHEDULE]++;
                        $time = $obj->rescheduleTime();
                        $this->logTaskInfo($task, "Task rescheduled for $time ({$cname})");
                        $this->storage->reschedule($task->id, $time);
                        break;
                    default:
                        $this->logWarning($task, "Task returned unknown status ({$cname})");
                        $this->storage->disable($task->id);
                        break;
                }
            } catch (\Exception $e) {
                $this->logWarning($task, "Task ({$cname}) threw exception: {$e->getMessage()}");
                $taskCounter[self::TASK_IMPOSSIBLE]++;
                $this->storage->disable($task->id);
            }
        }
        if ($weHadATask) {
            $this->logInfo(sprintf(
                "Finished task queue: Completed %d, Skipped %d, Impossible %d, Rescheduled %d",
                $taskCounter[0],
                $taskCounter[1],
                $taskCounter[2],
                $taskCounter[3]
            ));
        }
    }

    private function logInfo($msg)
    {
        $this->container->logger->info($msg);
    }

    private function logTaskInfo($task, $msg)
    {
        $this->container->logger->info("[task {$task->id}] ".$msg);
    }

    public function runIn($seconds, $class, array $params = [])
    {
        return $this->storage->add(time() + $seconds, $class, $params);
    }

    public function runAt(\DateTime $datetime, $class, array $params = [])
    {
        return $this->storage->add($datetime->getTimestamp(), $class, $params);
    }

    public function runNow($class, array $params = [])
    {
        return $this->storage->add(time(), $class, $params);
    }

    public function clearTasks($class, callable $filter)
    {
        if (!is_callable($filter)) {
            return 0;
        }
        $count = 0;
        foreach ($this->storage->iterator() as $task) {
            if (call_user_func($filter, $task)) {
                $this->storage->finish($task->id);
                $count++;
            }
        }
        return $count;
    }

    private function logWarning($task, $reason)
    {
        $this->container->logger->warning('TaskScheduler: '.$reason, (array)$task);
    }
}

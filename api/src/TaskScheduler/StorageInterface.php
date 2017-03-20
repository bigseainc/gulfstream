<?php

namespace BigSea\Gulfstream\API\TaskScheduler;

interface StorageInterface
{
    public function add($timestamp, $classname, $params);
    public function iterator();
    public function iteratorBefore($timestamp);
    public function iteratorWithFilter(callable $filter);
    public function finish($id); // release & delete
    public function requeue($id); // release and push back in queue
    public function reschedule($id, $timestamp); // release and push back in queue
    public function disable($id);
    public function enable($id);
    public function release($id);
}

<?php

namespace BigSea\Gulfstream\API\CacheHandler;

interface CacheInterface
{
    public function get($key);
    public function set($key, $data);
    public function delete($key);
}

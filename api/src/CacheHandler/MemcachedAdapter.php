<?php

namespace BigSea\Gulfstream\API\CacheHandler;

use Psr\Log\LoggerInterface;

class MemcachedAdapter implements CacheInterface
{
    private $server = null;
    private $logger;

    const DEFAULT_TIME_LIMIT = 900; // 15 minutes

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if (!class_exists('\Memcached')) {
            $this->logger->critical('Missing Memcached PHP Extension');
            throw new \Exception('MemcachedAdapter called without Memcached installed.');
        }

        $this->server = new \Memcached();
        $this->server->addServer('localhost', 11211);
    }

    public function set($key, $data, $timeLimit = null)
    {
        if (!$this->server) {
            return null;
        }

        if (!$timeLimit) {
            $timeLimit = time() + self::DEFAULT_TIME_LIMIT;
        }

        return $this->server->set($key, $data, $timeLimit);
    }

    public function get($key)
    {
        if (!$this->server) {
            return null;
        }
        
        return $this->server->get($key);
    }

    public function delete($key)
    {
        if (!$this->server) {
            return null;
        }

        return $this->server->delete($key);
    }
}

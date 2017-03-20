<?php

namespace BigSea\Gulfstream\API\Behaviors;

abstract class BaseBehavior
{
    protected $container;
    protected $logger;
    protected $core;
    protected $db;

    public function __construct($container)
    {
        $this->container = $container;
        $this->core = $container['core'];
        $this->logger = $container['logger'];
        $this->db = $container['database'];
    }

    public function implode(array $array)
    {
        $output = '(';
            $output .= "'{$array[0]}'";
        $output .= ')';

        return $output;
    }
}

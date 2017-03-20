<?php

namespace BigSea\Gulfstream\Generators;

class TokenGenerator
{
    const CHARACTERS = 'abcdefhijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWYXZ0123456789';
    private $container;
    private $db;

    public $code;

    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->db = $c['database'];
    }

    public function generate($length = 10)
    {
        $max = strlen(self::CHARACTERS) - 1;

        $this->code = '';
        while ($length > 0) {
            $this->code += self::CHARACTERS[rand(0, $max)];
            $length--;
        }

        return $this->code;
    }

    public function checkCode($databaseName, $key = 'id')
    {
    }
}

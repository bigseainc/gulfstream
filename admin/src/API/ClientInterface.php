<?php

namespace BigSea\Gulfstream\Admin\API;

interface ClientInterface
{
    public function get($route);
    public function post($route, $params);
    public function put($route, $params);
    public function delete($route);
    public function setToken($token);
}

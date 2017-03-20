<?php

namespace BigSea\Gulfstream\Admin\Handlers;

use Slim\Container;
use Psr\Http\Message\ResponseInterface;

class BaseHandler
{
    /** @var Slim\Container $container */
    protected $container;
    protected $api;
    protected $renderer;
    protected $data;
    protected $flash;

    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->renderer = $c['renderer'];
        $this->api = $c['api'];
        $this->flash = $c['flash'];

        $this->data = [
            'messages' => $this->flash->getMessages(),
        ];
    }

    protected function renderData(array $data = []) {
        if (!is_array($data)) {
            $this->container->logger->warn('Data for Rendering is Invalid');
            $data = [];
        }

        return array_merge($this->data, $data);
    }
}

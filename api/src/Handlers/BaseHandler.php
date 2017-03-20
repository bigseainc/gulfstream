<?php

namespace BigSea\Gulfstream\API\Handlers;

use Slim\Container;
use Psr\Http\Message\ResponseInterface;

class BaseHandler
{
    /** @var Slim\Container $container */
    protected $container;

    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->core = $c['core'];
    }

    protected function errorResponse(ResponseInterface $response, string $message, array $additionalDetails = [])
    {
        return $response->withJson([
            'status' => false,
            'message' => $message,
            'details' => $additionalDetails,
        ]);
    }

    protected function successResponse(ResponseInterface $response, array $data = [])
    {
        $defaultResponse = ['status' => true];
        unset($data['status']);

        return $response->withJson(array_merge($defaultResponse, $data));
    }

    protected function getParam(array &$params, $idx, $default = null, $filter = null)
    {
        if (isset($params[$idx])) {
            if ($filter !== null) {
                return call_user_func($filter, $params[$idx]);
            }
            return $params[$idx];
        }
        return $default;
    }
}

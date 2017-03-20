<?php
// Routes
$hNS = '\BigSea\Gulfstream\Admin\Handlers';

$app->add(function ($req, $res, $next) {
    try {
        $this->variables;
        $res = $next($req,$res);
    } catch (\Exception $e) {
        $this->logger->error($e->__toString());
        $res = $res->withStatus(500)->write($e->getMessage());
    }
    return $res;
});

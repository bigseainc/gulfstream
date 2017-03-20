<?php
// Routes
$hNS = '\BigSea\Gulfstream\API\Handlers';

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->add(function ($req, $res, $next) {
    try {
        $this->variables;
        $res = $next($req,$res);
    } catch (\Exception $e) {
        $this->logger->error($e->__toString());
        $res = $res->withStatus(500)->withJson([
            'status' => false,
            'systemFailure' => true,
            'message' => $e->getMessage(),
        ]);
    }
    return $res;
});

$app->get('/', function() {
    return $this->renderer->render('index.twig');
});

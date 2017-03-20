<?php

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $loader = new \Twig_Loader_Filesystem($settings['template_path']);
    $twig = new \Twig_Environment($loader, [
        'cache' => $settings['cache_path'],
    ]);

    if (file_exists(__DIR__ . '/twig.php')) {
        include (__DIR__ . '/twig.php');
    }

    return $twig;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['api'] = function ($c) {
    return new \BigSea\Gulfstream\Admin\API\Client(getenv('API_BASE'), $c['logger']);
};

$container['variables'] = function ($c) {
    try {
        $dotenv = new \Dotenv\Dotenv($c->get('settings')['dotenv']); // Project Root
        $dotenv->load();
        $dotenv->required([
            'API_BASE',
        ]);
    } catch (\Exception $e) {
        $c->logger->emergency($e->getMessage());
        throw new \Exception('Missing Environment Variables. See logs/app.log for more information');
    }
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::WARNING));
    return $logger;
};

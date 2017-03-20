<?php

$container['database'] = function ($c) {
    // PDO
    // $dsn = 'mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME');
    // return new \Slim\PDO\Database($dsn, getenv('DB_USER'), getenv('DB_PASS'));

    // Propel
    require_once __DIR__ . '/../database/config.php';
    // That's all folks.
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $loader = new \Twig_Loader_Filesystem($settings['template_path']);
    return new \Twig_Environment($loader, [
        'cache' => $settings['cache_path'],
    ]);
};

$container['core'] = function ($c) {
    return new \BigSea\Gulfstream\API\Behaviors\BehaviorFactory($c);
};

$container['variables'] = function ($c) {
    try {
        $dotenv = new \Dotenv\Dotenv($c->get('settings')['dotenv']); // Project Root
        $dotenv->load();
        $dotenv->required([
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASS',
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

$container['cache'] = function ($c) {
    if (class_exists('\Memcached')) {
        $logger = $c->logger;
        return new \BigSea\Gulfstream\API\CacheHandler\MemcachedAdapter($logger);
    }

    $c->logger->info('Caching not enabled');
    return new \BigSea\Gulfstream\API\CacheHandler\NullAdapter();
};

// tasks
$container['taskService'] = function ($c) {
    $storage = new \BigSea\Gulfstream\API\TaskScheduler\PDOStorage($c->database);
    return new \BigSea\Gulfstream\API\TaskScheduler($c, $storage);
};

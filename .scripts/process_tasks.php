<?php

require 'vendor/autoload.php';
$settings = require 'api/src/settings.php';

$container = new \Slim\Container($settings);
require 'api/src/dependencies.php';

$container->variables;
$ts = $container->taskService;

// usage examples:
//
// $result = $ts->runNow(\Namespace\TaskScheduler\Tasks\DebugTask::class, ['lol', 'wtf', 'bbq']);
//
// $result = $ts->runNow(\Namespace\TaskScheduler\Tasks\Emails\SendWelcomeEmail::class, [
//     'email' => 'devs@bigseadesign.com'
// ]);

$ts->process(60); // 1 minute time limit

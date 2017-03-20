<?php
require_once('../../vendor/autoload.php');
if (file_exists('../../.env')) {
    $dotenv = new \Dotenv\Dotenv('../../');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
}

return [
    'propel' => [
        'database' => [
            'connections' => [
                'default' => [
                    'adapter' => 'mysql',
                    'dsn' => 'mysql:host=%env.DB_HOST%;port=3306;dbname=%env.DB_NAME%',
                    'user' => '%env.DB_USER%',
                    'password' => '%env.DB_PASS%',
                    'settings' => [
                        'charset' => 'utf8mb4'
                    ]
                ]
            ]
        ]
    ]
];

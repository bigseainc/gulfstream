<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
            'cache_path' => false, //__DIR__ . '/../cache/twig/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'gulfstream-admin',
            'path' => __DIR__ . '/../../logs/admin.log',
        ],

        // Where the .ENV file is located
        'dotenv' => __DIR__ . '/../..',
    ],
];

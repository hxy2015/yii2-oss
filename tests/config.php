<?php
$config = [
    'oss' => [
        'hostname' => '127.0.0.1:8080',
        'bucket' => 'test',
        'accessId' => 'abcd',
        'accessKey' => 'abcd',
    ]
];

if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;

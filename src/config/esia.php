<?php

return [
    'test' => env('ESIA_TEST', true),

    'clientId' => env('ESIA_CLIENT_ID'),
    'redirectUrl' => '',
    'scope' => ['fullname', 'birthdate'],

    'signer' => Esia\Signer\CliSignerPKCS7::class,
    'certPath' => env('ESIA_CERT_PATH'),
    'privateKeyPath' => env('ESIA_PRIVATE_KEY_PATH'),
    'privateKeyPassword' => env('ESIA_PRIVATE_KEY_PASSWORD'),
    'tmpPath' => '/var/tmp',
];

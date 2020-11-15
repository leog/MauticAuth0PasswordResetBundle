<?php
return [
    'name'        => 'Auth0',
    'description' => 'Integración con funcionalidades de Auth0.',
    'author'      => 'Leo Giovanetti',
    'version'     => '1.0.0',
    'services' => [
        'events' => [
            'plugin.auth0.configbundle.subscriber' => [
                'class' => 'MauticPlugin\MauticAuth0Bundle\EventListener\ConfigSubscriber'
            ],
            'plugin.auth0.emailbundle.subscriber' => [
                'class' => 'MauticPlugin\MauticAuth0Bundle\EventListener\EmailSubscriber'
            ]
        ],
        'forms'  => array(
            'plugin.auth0.form' => array(
                'class' => 'MauticPlugin\MauticAuth0Bundle\Form\Type\Auth0Type',
                'alias' => 'auth0_config'
            )
        ),
    ],
    'parameters' => array(
        'auth0_management_api_token' => ''
    )
];
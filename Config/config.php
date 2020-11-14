<?php
return [
    'name'        => 'Auth0',
    'description' => 'Integración con funcionalidades de Auth0.',
    'author'      => 'Leo Giovanetti',
    'version'     => '1.0.0',
    'routes'   => [
        'main' => [
            'plugin_auth0_admin' => array(
                'path'       => '/auth0/admin',
                'controller' => 'MauticAuth0Bundle:Default:admin'
            )
        ]
    ],
    'menu' => [
        'admin' => [
            'plugin.auth0.admin' => [
                'route'     => 'plugin_auth0_admin',
                'iconClass' => 'fa-gears',
                'access'    => 'admin',
                'checks'    => array(
                    'parameters' => array(
                        'auth0_management_api_token' => true
                    )
                ),
                'priority'  => 60
            ]
        ]
    ],
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
                'class' => 'MauticPlugin\MauticAuth0Bundle\Form\Type\ConfigType',
                'alias' => 'auth0'
            )
        ),
    ],
    'parameters' => array(
        'auth0_management_api_token' => ''
    )
];
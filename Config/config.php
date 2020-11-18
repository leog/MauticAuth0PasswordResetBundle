<?php
return [
    'name'        => 'Auth0 Password Reset',
    'description' => 'Providing a Password Reset URL Token to send to users. Requires a custom field "auth0userid" for a contact.',
    'author'      => 'Leo Giovanetti',
    'version'     => '1.0.0',
    'services' => [
        'events' => [
            'mautic.auth0.configbundle.subscriber' => [
                'class' => 'MauticPlugin\MauticAuth0PasswordResetBundle\EventListener\ConfigSubscriber'
            ],
            'mautic.auth0.emailbundle.subscriber' => [
                'class' => 'MauticPlugin\MauticAuth0PasswordResetBundle\EventListener\EmailSubscriber',
                'arguments' => ['mautic.helper.core_parameters']
            ]
        ],
        'forms'  => array(
            'plugin.auth0_password_reset.form' => array(
                'class' => 'MauticPlugin\MauticAuth0PasswordResetBundle\Form\Type\Auth0Type',
                'alias' => 'auth0_password_reset_config'
            )
        ),
    ],
    'parameters' => array(
        'auth0_domain_url' => '',
        'auth0_result_url' => '',
        'auth0_client_id' => '',
        'auth0_client_secret' => ''
    )
];
<?php

namespace MauticPlugin\MauticAuth0PasswordResetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 * @package Mautic\FormBundle\Form\Type
 */
class Auth0Type extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'auth0_domain_url',
            'text',
            array(
                'label' => 'plugin.mauticauth0passwordreset.config.auth0_domain_url',
                'data'  => $options['data']['auth0_domain_url'],
                'attr'  => array(
                    'tooltip' => 'plugin.mauticauth0passwordreset.config.auth0_domain_url_tooltip',
                )
            )
        );

        $builder->add(
            'auth0_result_url',
            'text',
            array(
                'label' => 'plugin.mauticauth0passwordreset.config.auth0_result_url',
                'data'  => $options['data']['auth0_result_url'],
                'attr'  => array(
                    'tooltip' => 'plugin.mauticauth0passwordreset.config.auth0_result_url_tooltip',
                )
            )
        );

        $builder->add(
            'auth0_client_id',
            'text',
            array(
                'label' => 'plugin.mauticauth0passwordreset.config.auth0_client_id',
                'data'  => $options['data']['auth0_client_id'],
            )
        );

        $builder->add(
            'auth0_client_secret',
            'text',
            array(
                'label' => 'plugin.mauticauth0passwordreset.config.auth0_client_secret',
                'data'  => $options['data']['auth0_client_secret'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mauticauth0passwordreset_config';
    }
}
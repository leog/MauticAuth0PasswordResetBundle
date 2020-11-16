<?php

namespace MauticPlugin\MauticAuth0Bundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            'auth0_management_api_token',
            'textarea',
            array(
                'label' => 'plugin.auth0.config.auth0_management_api_token',
                'data'  => $options['data']['auth0_management_api_token'],
                'attr'  => array(
                    'tooltip' => 'plugin.auth0.config.auth0_management_api_token_tooltip',
                )
            )
        );

        $builder->add(
            'auth0_domain_url',
            'text',
            array(
                'label' => 'plugin.auth0.config.auth0_domain_url',
                'data'  => $options['data']['auth0_domain_url'],
                'attr'  => array(
                    'tooltip' => 'plugin.auth0.config.auth0_domain_url_tooltip',
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'auth0_config';
    }
}
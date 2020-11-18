<?php

namespace MauticPlugin\MauticAuth0PasswordResetBundle\EventListener;

use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;

/**
 * Class ConfigSubscriber
 */
class ConfigSubscriber extends CommonSubscriber
{

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            ConfigEvents::CONFIG_ON_GENERATE => array('onConfigGenerate', 0),
            ConfigEvents::CONFIG_PRE_SAVE    => array('onConfigSave', 0)
        );
    }

    /**
     * @param ConfigBuilderEvent $event
     */
    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm(
            array(
                'formAlias'  => 'auth0_password_reset_config',
                'formTheme'  => 'MauticAuth0PasswordResetBundle:FormTheme\Config',
                'parameters' => $event->getParametersFromConfig('MauticAuth0PasswordResetBundle')
            )
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public function onConfigSave(ConfigEvent $event)
    {
        /** @var array $values */
        $values = $event->getConfig();

        // Manipulate the values
        if (!empty($values['auth0_password_reset_config']['auth0_domain_url'])) {
            $values['auth0_password_reset_config']['auth0_domain_url'] = htmlspecialchars($values['auth0_password_reset_config']['auth0_domain_url']);
        }

        if (!empty($values['auth0_password_reset_config']['auth0_result_url'])) {
            $values['auth0_password_reset_config']['auth0_result_url'] = htmlspecialchars($values['auth0_password_reset_config']['auth0_result_url']);
        }

        if (!empty($values['auth0_password_reset_config']['auth0_client_id'])) {
            $values['auth0_password_reset_config']['auth0_client_id'] = htmlspecialchars($values['auth0_password_reset_config']['auth0_client_id']);
        }

        if (!empty($values['auth0_password_reset_config']['auth0_client_secret'])) {
            $values['auth0_password_reset_config']['auth0_client_secret'] = htmlspecialchars($values['auth0_password_reset_config']['auth0_client_secret']);
        }

        // Set updated values 
        $event->setConfig($values);
    }
}

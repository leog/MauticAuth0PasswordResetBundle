<?php

namespace MauticPlugin\MauticAuth0PasswordResetBundle\EventListener;

use Mautic\ConfigBundle\Event\ConfigEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;

/**
 * Class ConfigSubscriber
 */
class ConfigSubscriber implements EventSubscriberInterface
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
                'formAlias'  => 'mauticauth0passwordreset_config',
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
        if (!empty($values['mauticauth0passwordreset_config']['auth0_domain_url'])) {
            $values['mauticauth0passwordreset_config']['auth0_domain_url'] = htmlspecialchars($values['mauticauth0passwordreset_config']['auth0_domain_url']);
        }

        if (!empty($values['mauticauth0passwordreset_config']['auth0_result_url'])) {
            $values['mauticauth0passwordreset_config']['auth0_result_url'] = htmlspecialchars($values['mauticauth0passwordreset_config']['auth0_result_url']);
        }

        if (!empty($values['mauticauth0passwordreset_config']['auth0_client_id'])) {
            $values['mauticauth0passwordreset_config']['auth0_client_id'] = htmlspecialchars($values['mauticauth0passwordreset_config']['auth0_client_id']);
        }

        if (!empty($values['mauticauth0passwordreset_config']['auth0_client_secret'])) {
            $values['mauticauth0passwordreset_config']['auth0_client_secret'] = htmlspecialchars($values['mauticauth0passwordreset_config']['auth0_client_secret']);
        }

        // Set updated values 
        $event->setConfig($values);
    }
}

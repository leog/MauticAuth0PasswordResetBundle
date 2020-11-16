<?php

namespace MauticPlugin\MauticAuth0Bundle\EventListener;

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
                'formAlias'  => 'auth0_config',
                'formTheme'  => 'MauticAuth0Bundle:FormTheme\Config',
                'parameters' => $event->getParametersFromConfig('MauticAuth0Bundle')
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
        if (!empty($values['auth0_config']['auth0_management_api_token'])) {
            $values['auth0_config']['auth0_management_api_token'] = htmlspecialchars($values['auth0_config']['auth0_management_api_token']);
        }

        if (!empty($values['auth0_config']['auth0_domain_url'])) {
            $values['auth0_config']['auth0_domain_url'] = htmlspecialchars($values['auth0_config']['auth0_domain_url']);
        }

        // Set updated values 
        $event->setConfig($values);
    }
}

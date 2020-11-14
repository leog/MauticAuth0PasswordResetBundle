<?php

namespace Mautic\ConfigBundle\Controller;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\ConfigBundle\Form\Type\ConfigType;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Helper\CacheHelper;
use Mautic\CoreBundle\Helper\EncryptionHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FormController
{
    /**
     * Controller action for editing the application configuration.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminAction()
    {
        //admin only allowed
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $event      = new ConfigBuilderEvent($this->get('mautic.helper.bundle'));
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(ConfigEvents::CONFIG_ON_GENERATE, $event);
        $fileFields  = $event->getFileFields();
        $formThemes  = $event->getFormThemes();
        $formConfigs = $this->get('mautic.config.mapper')->bindFormConfigsWithRealValues($event->getForms());

        $this->mergeParamsWithLocal($formConfigs);

        // Create the form
        $action = $this->generateUrl('plugin_auth0_admin');
        $form   = $this->get('form.factory')->create(
            ConfigType::class,
            $formConfigs,
            [
                'action'     => $action,
                'fileFields' => $fileFields,
            ]
        );

        $originalNormData = $form->getNormData();

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator = $this->get('mautic.configurator');
        $isWritabale  = $configurator->isFileWritable();
        $openTab      = null;

        // Check for a submitted form and process it
        if ('POST' == $this->request->getMethod()) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                $isValid = false;
                if ($isWritabale && $isValid = $this->isFormValid($form)) {
                    // Bind request to the form
                    $post     = $this->request->request;
                    $formData = $form->getData();

                    // Dispatch pre-save event. Bundles may need to modify some field values like passwords before save
                    $configEvent = new ConfigEvent($formData, $post);
                    $configEvent
                        ->setOriginalNormData($originalNormData)
                        ->setNormData($this->filterNormDataForLogging($form->getNormData()));
                    $dispatcher->dispatch(ConfigEvents::CONFIG_PRE_SAVE, $configEvent);
                    $formValues = $configEvent->getConfig();

                    $errors      = $configEvent->getErrors();
                    $fieldErrors = $configEvent->getFieldErrors();

                    if ($errors || $fieldErrors) {
                        foreach ($errors as $message => $messageVars) {
                            $form->addError(
                                new FormError($this->translator->trans($message, $messageVars, 'validators'))
                            );
                        }

                        foreach ($fieldErrors as $key => $fields) {
                            foreach ($fields as $field => $fieldError) {
                                $form[$key][$field]->addError(
                                    new FormError($this->translator->trans($fieldError[0], $fieldError[1], 'validators'))
                                );
                            }
                        }
                        $isValid = false;
                    } else {
                        // Prevent these from getting overwritten with empty values
                        $unsetIfEmpty = $configEvent->getPreservedFields();
                        $unsetIfEmpty = array_merge($unsetIfEmpty, $fileFields);

                        try {
                            // Ensure the config has a secret key
                            $params = $configurator->getParameters();
                            if (empty($params['secret_key'])) {
                                $configurator->mergeParameters(['secret_key' => EncryptionHelper::generateKey()]);
                            }

                            $configurator->write();
                            $dispatcher->dispatch(ConfigEvents::CONFIG_POST_SAVE, $configEvent);

                            $this->addFlash('mautic.config.config.notice.updated');

                            /** @var CacheHelper $cacheHelper */
                            $cacheHelper = $this->get('mautic.helper.cache');
                            $cacheHelper->refreshConfig();

                            if ($isValid && !empty($formData['coreconfig']['last_shown_tab'])) {
                                $openTab = $formData['coreconfig']['last_shown_tab'];
                            }
                        } catch (\RuntimeException $exception) {
                            $this->addFlash('mautic.config.config.error.not.updated', ['%exception%' => $exception->getMessage()], 'error');
                        }
                    }
                } elseif (!$isWritabale) {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('mautic.config.notwritable')
                        )
                    );
                }
            }
            // If the form is saved or cancelled, redirect back to the dashboard
            if ($cancelled || $isValid) {
                if (!$cancelled && $this->isFormApplied($form)) {
                    return $this->delegateRedirect($this->generateUrl('plugin_auth0_admin'));
                } else {
                    return $this->delegateRedirect($this->generateUrl('mautic_dashboard_index'));
                }
            }
        }

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'        => $tmpl,
                    'security'    => $this->get('mautic.security'),
                    'form'        => $this->setFormTheme($form, 'MauticAuth0Bundle:Config:form.html.php', $formThemes),
                    'formConfigs' => $formConfigs,
                    'isWritable'  => $isWritabale,
                ],
                'contentTemplate' => 'MauticAuth0Bundle:Config:form.html.php',
            ]
        );
    }
}
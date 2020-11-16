<?php

namespace MauticPlugin\MauticAuth0Bundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class EmailSubscriber
 */
class EmailSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            EmailEvents::EMAIL_ON_BUILD   => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND    => ['decodeTokensSend', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['decodeTokensDisplay', 0],
        );
    }

    /**
     * @param EmailBuilderEvent $event
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function onEmailBuild(EmailBuilderEvent $event)
    {
        // register tokens
        $tokens = [
            '{password_reset_ticket_url}' => $this->translator->trans('plugin.auth0.password_reset_ticket_url.token'),
        ];

        if ($event->tokensRequested($tokens)) {
            $event->addTokens(
                $event->filterTokens($tokens)
            );
        }
    }

    /**
     * Search and replace tokens with content.
     *
     * @param EmailSendEvent $event
     *
     * @throws \RuntimeException
     */
    public function decodeTokensDisplay(EmailSendEvent $event)
    {
        $this->decodeTokens($event);
    }

    /**
     * Search and replace tokens with content.
     *
     * @param EmailSendEvent $event
     *
     * @throws \RuntimeException
     */
    public function decodeTokensSend(EmailSendEvent $event)
    {
        $this->decodeTokens($event);
    }

    /**
     * Search and replace tokens with content.
     *
     * @param EmailSendEvent $event
     *
     * @throws \RuntimeException
     */
    public function decodeTokens(EmailSendEvent $event)
    {
        $tokens = [];
        $lead = $event->getLead();
        $content = "THE_URL";
        $tokens['{password_reset_ticket_url}'] = $content;
        $event->addTokens($tokens);
    }
}

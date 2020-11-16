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
        $config = $this->get('mautic.helper.core_parameters');
        $auth0ManagementApiToken = $config->getParameter('auth0_management_api_token');
        $auth0DomainUrl = $config->getParameter('auth0_domain_url');

        $response        = new JsonResponse();
        $responseContent = [
            'ticket'       => ''
        ];

        $client = HttpClient::create([
            'headers' => [
                'Authorization' => 'Bearer '.$auth0ManagementApiToken,
                'Content-Type' => 'application/json'
            ]
        ]);

        $parameters    = [
            "result_url" => "http://myapp.com/callback",
            "user_id" => "",
            "connection_id" => "con_0000000000000001",
            "email" => "",
            "ttl_sec" => 0,
            "mark_email_as_verified" => false,
            "includeEmailInRedirect" => false,
            'oauth2_access_token' => $accessToken['access_token'],
            'format'              => 'json',
        ];

        $url = "https://".$auth0DomainUrl."/api/v2/tickets/password-change";

        try {
            /** @var ResponseInterface $httpResponse */
            $httpResponse = $client->request(Request::METHOD_POST, $url);
        } catch (\Exception $e) {
            $this->logger->debug("Exception Auth0 EmailSubscriber: ".$e->getMessage());
            $this->addFlash('plugin.auth0.config.auth0_management_api_token.error', [], 'error');
        } 

        //$this->addFlash('mautic.config.config.error.not.updated', ['%exception%' => $exception->getMessage()], 'error');
        //$this->logger->debug("Mark email '".$bouncedRecipient['emailAddress']."' as bounced, reason: ".$bounceCode);

        $data = $this->makeRequest($url, $parameters, 'POST', ['auth_type' => 'rest']);

        $tokens['{password_reset_ticket_url}'] = $content;
        $event->addTokens($tokens);
    }
}

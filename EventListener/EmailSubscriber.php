<?php

namespace MauticPlugin\MauticAuth0PasswordResetBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;

use Http\Client\HttpClient;
use Mautic\CoreBundle\Helper\CoreParametersHelper;

/**
 * Class EmailSubscriber
 */
class EmailSubscriber extends CommonSubscriber
{
    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;
    protected $auth0ManagementApiToken;

    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
    }

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
            '{password_reset_ticket_url}' => $this->translator->trans('plugin.auth0_password_reset.password_reset_ticket_url.token'),
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

    public function getAuth0Token()
    {
        $curl = curl_init();

        $auth0DomainUrl = $this->coreParametersHelper->getParameter('auth0_domain_url');
        $auth0ClientId = $this->coreParametersHelper->getParameter('auth0_client_id');
        $auth0ClientSecret = $this->coreParametersHelper->getParameter('auth0_client_secret');

        $payload = [
            "grant_type" => 'client_credentials',
            "client_id" => $auth0ClientId,
            "client_secret" => $auth0ClientSecret,
            "audience" => 'https://'.$auth0DomainUrl.'/api/v2/'
        ];

        if (!empty($auth0DomainUrl) && !empty($auth0ClientId) && !empty($auth0ClientSecret)) {
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://".$auth0DomainUrl."/oauth/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            if ($err) {
                throw new \Exception('Request to Auth0 Management API failed: ' . $err);
            } else {
                $data = json_decode($response, true);
                if (curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200) {
                    curl_close($curl);
                    return $data["access_token"];
                } else {
                    throw new \Exception('Request to Auth0 Management API failed: ' . json_encode($data));
                }
            }
        } else {
            throw new \Exception('Empty required value: auth0DomainUrl="' . $auth0DomainUrl . '" / auth0ClientId="' . $auth0ClientId . '" / auth0ClientSecret="' . $auth0ClientSecret);
        }
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
        $content = $event->getContent();
        if (preg_match("/{password_reset_ticket_url}/", $content)) {
            $tokens = [];
            $lead = $event->getLead();
            $lead_auth0_user_id = $lead["auth0userid"];
            if(empty($this->auth0ManagementApiToken)) {
                $this->auth0ManagementApiToken = $this->getAuth0Token();
            }
            $auth0DomainUrl = $this->coreParametersHelper->getParameter('auth0_domain_url');
            $auth0ResultUrl = $this->coreParametersHelper->getParameter('auth0_result_url');

            if (!empty($lead_auth0_user_id) && !empty($this->auth0ManagementApiToken) && !empty($auth0DomainUrl) && !empty($auth0ResultUrl)) {
                $curl = curl_init();
                $url = "https://" . $auth0DomainUrl . "/api/v2/tickets/password-change";
                $payload = [
                    "result_url" => $auth0ResultUrl,
                    "user_id" => $lead_auth0_user_id,
                    "ttl_sec" => 0,
                    "mark_email_as_verified" => false,
                    "includeEmailInRedirect" => false
                ];

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer " . $this->auth0ManagementApiToken,
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    throw new \Exception('Request to Auth0 Management API failed: ' . $err);
                } else {
                    $data = json_decode($response, true);
                    $this->logger->debug("OK Auth0 EmailSubscriber: " . $response);
                    $content = $data["ticket"];
                    $tokens['{password_reset_ticket_url}'] = $content;
                    $event->addTokens($tokens);
                }
            } else {
                throw new \Exception('Empty required value: lead_auth0_user_id="' . $lead_auth0_user_id . '" / auth0ManagementApiToken="' . $this->auth0ManagementApiToken . '" / auth0DomainUrl="' . $auth0DomainUrl . '" / auth0ResultUrl="' . $auth0ResultUrl . '"');
            }
        }
    }
}

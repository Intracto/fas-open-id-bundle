<?php

namespace Intracto\FasOpenIdBudle\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OAuthClient
{
    /**
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $nonce;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * OAuthClient constructor.
     *
     * @param array $scope
     * @param string $clientId
     * @param string $clientSecret
     * @param string $state
     * @param string $nonce
     * @param string $baseUrl
     */
    public function __construct(array $scope, string $clientId, string $clientSecret, string $state, string $nonce, string $redirectUriPath, string $baseUrl)
    {
        $this->scope = $scope;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->state = $state;
        $this->nonce = $nonce;
        $this->baseUrl = $baseUrl;
        $this->redirectUri = $redirectUriPath;
        $this->httpClient = HttpClient::create(['base_uri' => $baseUrl]);
    }

    public function getAccessToken(string $code): void
    {

    }
}

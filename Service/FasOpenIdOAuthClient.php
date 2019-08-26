<?php

namespace Intracto\FasOpenIdBundle\Service;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Intracto\FasOpenIdBundle\Model\OAuthTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FasOpenIdOAuthClient
{
    public const SCOPE_OPEN_ID = 'openid';
    public const SCOPE_PROFILE = 'profile';
    public const SCOPE__EGOVNRN = 'egovnrn';
    public const SCOPE_CERTIFICATE_INFO = 'certificateInfo';
    public const SCOPE_CITIZEN = 'citizen';
    public const SCOPE_ENTERPRISE = 'enterprise';
    public const SCOPE_ROLES = 'roles';
    
    public const ACR_VALUES_E_ID = 'urn:be:fedict:iam:fas:Level500';
    public const ACR_VALUES_ITS_ME = 'urn:be:fedict:iam:fas:Level450';
    public const ACR_VALUES_MOBILE_APP = 'urn:be:fedict:iam:fas:Level400';
    public const ACR_VALUES_USERNAME_PASSWORD = 'urn:be:fedict:iam:fas:Level200';
    public const ACR_VALUES_SELF_REGISTRATION = 'urn:be:fedict:iam:fas:Level100';

    private const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    private const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

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
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OAuthClient constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param array $scope
     * @param string $redirectUriPath
     * @param string $baseUrl
     * @param UrlGeneratorInterface $urlGenerator
     *
     * @throws \Exception
     */
    public function __construct(string $clientId, string $clientSecret, array $scope, string $redirectUriPath, string $baseUrl, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scope = array_unique(array_merge($scope, [self::SCOPE_OPEN_ID]));

        $this->baseUrl = $baseUrl;
        $this->urlGenerator = $urlGenerator;
        $this->redirectUri = $this->urlGenerator->generate($redirectUriPath, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->logger = $logger;
        $this->httpClient = HttpClient::create(['base_uri' => $baseUrl]);

        $this->state = random_int(10000,1000000);
        $this->nonce = random_int(10000,1000000);
    }

    public function getAuthenticationUrl(string $acrValue): string
    {
        $url = $this->baseUrl.'authorize?';
        $url .= 'response_type=code';
        $url .= '&client_id='.$this->clientId;
        $url .= '&scope='.rawurlencode(implode(' ', $this->scope));
        $url .= '&acr_values='.$acrValue;
        $url .= '&redirect_uri='.$this->redirectUri;
        $url .= '&state='.$this->state;
        $url .= '&nonce='.$this->nonce;

        return $url;
    }

    /**
     * @param string $code
     *
     * @return OAuthToken|null
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getAccessToken(string $code): ?OAuthToken
    {
        $requestBody = [
            'grant_type' => self::GRANT_TYPE_AUTHORIZATION_CODE,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ];

        $response = $this->httpClient->request('POST', 'access_token', [
            'body' => $requestBody,
            'auth_basic' => [$this->clientId, $this->clientSecret],
        ]);

        if (Response::HTTP_OK === $response->getStatusCode()) {
            return $this->createAccessTokenFromResponse($response->toArray(false));
        }

        $this->logger->error($response->getContent(false));
        $this->logger->error($response->getInfo('debug'));
        throw new AuthenticationException('Failed to fetch access token');
    }

    /**
     * Call to get user information from current user.
     *
     * @param OAuthToken $oauthToken
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @return null|array
     */
    public function getUserInfo(OAuthToken $oauthToken): ?string
    {
        if (!$this->verifyAccessToken($oauthToken->getAccessToken())) {
            $oauthToken = $this->getRefreshToken($oauthToken->getRefreshToken());

            if (null === $oauthToken) {
                return null;
            }
        }

        try {
            $response = $this->httpClient->request('GET', 'userinfo', ['auth_bearer' => $oauthToken->getAccessToken()]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        if (Response::HTTP_OK === $response->getStatusCode()) {
            try {
                $data = $response->getContent();
            } catch (ClientExceptionInterface $e) {
                return null;
            } catch (RedirectionExceptionInterface $e) {
                return null;
            } catch (ServerExceptionInterface $e) {
                return null;
            } catch (TransportExceptionInterface $e) {
                return null;
            }

            return $data;
        }

        return null;
    }

    public function logOut(OAuthTokenInterface $oauthToken): void
    {
        if ($oauthToken->getExpiresIn() < new \DateTime()) {
            $oauthToken = $this->getRefreshToken($oauthToken->getRefreshToken());
        }

        $response = $this->httpClient->request('GET', 'connect/endSession', ['query' => ['id_token_hint' => $oauthToken->getIdToken()]]);

        if (Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
            $this->logger->error($response->getInfo('debug'), ['status_code' => $response->getStatusCode()]);

            throw new \Exception($response->getContent(false));
        }
    }

    public function getLogoutUrl(string $idToken): string
    {
        $url = $this->baseUrl.'connect/endSession';
        $url .= '?id_token_hint='.$idToken;
        $url .= '&post_logout_redirect_uri='.$this->urlGenerator->generate('app.security.logout', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    public function getPublicKeys(): array
    {
        $response = $this->httpClient->request('GET', 'connect/jwk_uri');

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Error on fetching public keys from FAS');
        }

        return json_decode($response->getContent(false), false)->keys;
    }

    public static function getAllPossibleScopes(): array
    {
        return [
            self::SCOPE_OPEN_ID,
            self::SCOPE_PROFILE,
            self::SCOPE__EGOVNRN,
            self::SCOPE_CERTIFICATE_INFO,
            self::SCOPE_CITIZEN,
            self::SCOPE_ENTERPRISE,
            self::SCOPE_ROLES,
        ];
    }

    private function getRefreshToken(string $refreshToken): ?OAuthToken
    {
        $requestBody = [
            'grant_type' => self::GRANT_TYPE_REFRESH_TOKEN,
            'refresh_token' => $refreshToken,
        ];

        $response = $this->httpClient->request('POST', 'access_token', [
            'body' => $requestBody,
            'auth_basic' => [$this->clientId, $this->clientSecret],
        ]);

        if (Response::HTTP_OK === $response->getStatusCode()) {
            return $this->createAccessTokenFromResponse($response->toArray(false));
        }

        $this->logger->info($response->getInfo('debug'));

        return null;
    }

    private function verifyAccessToken(string $accessToken): bool
    {
        $response = $this->httpClient->request('POST', 'introspect', ['auth_basic' => [$this->clientId, $this->clientSecret], 'query' => ['token' => $accessToken]]);
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->logger->info($response->getInfo('debug'));

            return false;
        }

        $body = $response->toArray(false);

        return (bool) $body['active'];
    }

    private function createAccessTokenFromResponse(array $data): OAuthToken
    {
        $expires = (new \DateTime())->add(new \DateInterval(sprintf('PT%dS', $data['expires_in'])));
        $token = new OAuthToken();
        $token->setAccessToken($data['access_token']);
        $token->setRefreshToken($data['refresh_token']);
        $token->setExpiresIn($expires);
        $token->setScope(explode(' ', $data['scope']));
        $token->setIdToken($data['id_token']);

        return $token;
    }
}

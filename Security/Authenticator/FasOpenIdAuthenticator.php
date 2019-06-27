<?php

namespace Intracto\FasOpenIdBundle\Security\Authenticator;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Intracto\FasOpenIdBundle\Security\Authentication\Token\FasOpenIdUserToken;
use Intracto\FasOpenIdBundle\Security\Provider\UserProvider;
use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\HttpUtils;

class FasOpenIdAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var string
     */
    private $authenticationPath;

    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var string
     */
    private $loginPath;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var FasOpenIdOAuthClient
     */
    private $oauthClient;

    /**
     * @var string
     */
    private $userInfo;

    /**
     * @var OAuthToken
     */
    private $oauthToken;

    /**
     * FasOpenIdAuthenticator constructor.
     *
     * @param string $authenticationPath
     * @param string $targetPath
     * @param string $loginPath
     * @param HttpUtils $httpUtils
     * @param FasOpenIdOAuthClient $oauthClient
     */
    public function __construct(string $authenticationPath, string $targetPath, string $loginPath, HttpUtils $httpUtils, FasOpenIdOAuthClient $oauthClient)
    {
        $this->authenticationPath = $authenticationPath;
        $this->targetPath = $targetPath;
        $this->loginPath = $loginPath;
        $this->httpUtils = $httpUtils;
        $this->oauthClient = $oauthClient;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): bool
    {
        return $this->authenticationPath === $request->attributes->get('_route') && $request->query->has('code');
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request): array
    {
        return ['code' => $request->query->get('code')];
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (!$userProvider instanceof UserProvider) {
            throw new \RuntimeException('Please provide the intracto_fas_open_id_user_provider as userprovider');
        }
        if (null === $credentials['code']) {
            throw new AuthenticationException('No authorization code provided');
        }

        try {
            $oauthToken = $this->oauthClient->getAccessToken($credentials['code']);
        } catch (\Exception $exception) {
            throw new AuthenticationException($exception->getMessage());
        }

        $this->oauthToken = $oauthToken;

        return $userProvider->creatUserByOAuthToken($oauthToken);
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        return $this->httpUtils->createRedirectResponse($request, $this->targetPath);
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null): ?Response
    {
        return $this->httpUtils->createRedirectResponse($request, $this->loginPath);
    }

    public function createAuthenticatedToken(UserInterface $user, $providerKey): TokenInterface
    {
        return new FasOpenIdUserToken($user, $this->oauthToken);
    }
}

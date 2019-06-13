<?php

namespace Intracto\FasOpenIdBundle\Security\Authenticator;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Intracto\FasOpenIdBundle\Security\Authentication\Token\FasOpenIdUserToken;
use Intracto\FasOpenIdBundle\Security\User\User;
use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Intracto\FasOpenIdBundle\Util\JwtTokenValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $userClass;

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
     * @param array $scope
     * @param string $userClass
     * @param HttpUtils $httpUtils
     * @param FasOpenIdOAuthClient $oauthClient
     */
    public function __construct(string $authenticationPath, string $targetPath, array $scope, string $userClass, HttpUtils $httpUtils, FasOpenIdOAuthClient $oauthClient)
    {
        $this->authenticationPath = $authenticationPath;
        $this->targetPath = $targetPath;
        $this->scope = $scope;
        $this->userClass = $userClass;
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
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials['code']) {
            return null;
        }

        try {
            $oauthToken = $this->oauthClient->getAccessToken($credentials['code']);
        } catch (\Exception $exception) {
            return null;
        }

        $this->oauthToken = $oauthToken;

        if (null === ($userInfo = $this->oauthClient->getUserInfo($oauthToken))) {
            return null;
        }
        $this->userInfo = $userInfo;

        try {
            $userInfo = JwtTokenValidator::validateToken($userInfo, $this->oauthClient->getPublicKeys());
        } catch (AuthenticationException $authenticationException) {
            return null;
        }

        $user = new $this->userClass();

        if (in_array(FasOpenIdOAuthClient::SCOPE__EGOVNRN, $this->scope, true)) {
            $user->setNationalInsuranceNumber($userInfo->egovNRN);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_PROFILE, $this->scope, true)) {
//            $user->setFirstName($userInfo->surName);
//            $user->setLastName($userInfo->givenName);
//            $user->setPrefLanguage($userInfo->PrefLanguage);
//            $user->setEmail($userInfo->mail);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_CERTIFICATE_INFO, $this->scope, true)) {
            $user->setCertIssuer($userInfo->cert_issuer);
            $user->setCertSubject($userInfo->cert_subject);
            $user->setCertSerialNumber($userInfo->cert_serialnumber);
            $user->setCertCn($userInfo->cert_cn);
            $user->setCertGivenName($userInfo->cert_givenname);
            $user->setCertSn($userInfo->cert_sn);
            $user->setCertMail($userInfo->cert_mail);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_ROLES, $this->scope, true)) {
            $user->setFasRoles($userInfo->roles);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
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
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->httpUtils->createRedirectResponse($request, $this->targetPath);
    }

    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        return new FasOpenIdUserToken($user, $this->userInfo, $this->oauthToken);
    }
}

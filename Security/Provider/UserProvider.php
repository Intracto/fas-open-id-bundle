<?php

namespace Intracto\FasOpenIdBundle\Security\Provider;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Intracto\FasOpenIdBundle\Model\OAuthTokenInterface;
use Intracto\FasOpenIdBundle\Security\User\User;
use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Intracto\FasOpenIdBundle\Util\JwtTokenValidator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var FasOpenIdOAuthClient
     */
    private $oauthClient;

    /**
     * @var OAuthToken
     */
    private $oauthToken;

    /**
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $userInfo;

    /**
     * UserProvider constructor.
     *
     * @param FasOpenIdOAuthClient $oauthClient
     * @param array $scope
     * @param string $userClass
     */
    public function __construct(FasOpenIdOAuthClient $oauthClient, array $scope, string $userClass)
    {
        $this->oauthClient = $oauthClient;
        $this->userClass = $userClass;
        $this->scope = $scope;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username): UserInterface
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in your User class.
        throw new \Exception('TODO: fill in loadUserByUsername() inside '.__FILE__);
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass($class): bool
    {
        return $class instanceof User;
    }

    public function creatUserByOAuthToken(OAuthTokenInterface $oauthToken): UserInterface
    {
        $user = new $this->userClass();
        if (null === ($userInfo = $this->oauthClient->getUserInfo($oauthToken))) {
            throw new UsernameNotFoundException(sprintf('No username found for access token %s', $oauthToken->getAccessToken()));
        }
        $this->userInfo = $userInfo;
        $user->setUserInfo($userInfo);

        try {
            $userInfo = JwtTokenValidator::validateToken($userInfo, $this->oauthClient->getPublicKeys());
        } catch (AuthenticationException $authenticationException) {
            throw new UsernameNotFoundException($authenticationException->getMessage());
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE__EGOVNRN, $this->scope, true)) {
            $user->setNationalInsuranceNumber($userInfo->egovNRN);
        }

        if (in_array(FasOpenIdOAuthClient::SCOPE_PROFILE, $this->scope, true)) {
            $user->setFirstName($userInfo->surname);
            $user->setLastName($userInfo->givenName);
            $user->setPrefLanguage($userInfo->prefLanguage);
            $user->setEmail($userInfo->mail);
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
}

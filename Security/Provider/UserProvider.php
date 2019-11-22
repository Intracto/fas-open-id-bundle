<?php

namespace Intracto\FasOpenIdBundle\Security\Provider;

use Intracto\FasOpenIdBundle\Model\OAuthTokenInterface;
use Intracto\FasOpenIdBundle\Security\User\User;
use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Intracto\FasOpenIdBundle\Util\JwtTokenValidator;
use Intracto\FasOpenIdBundle\Util\UserInfoMapper;
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
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $userClass;

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

        try {
            $userInfo = JwtTokenValidator::validateToken($userInfo, $this->oauthClient->getPublicKeys());
        } catch (AuthenticationException $authenticationException) {
            throw new UsernameNotFoundException($authenticationException->getMessage());
        }

        return UserInfoMapper::mapUserInfoToUserObject($user, $userInfo, $this->scope);
    }
}

<?php

namespace Intracto\FasOpenIdBundle\Security\Authentication\Token;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class FasOpenIdUserToken extends AbstractToken
{
    /**
     * @var string
     */
    private $userInfo;

    /**
     * @var OAuthToken
     */
    private $oauthToken;

    public function __construct(UserInterface $user, string $userInfo, OAuthToken $oauthToken, array $roles = [])
    {
        parent::__construct(['ROLE_USER']);

        $this->setUser($user);
        $this->userInfo = $userInfo;
        $this->oauthToken = $oauthToken;
        $this->setAuthenticated(true);
    }

    /**
     * @inheritDoc
     */
    public function getCredentials()
    {
        return '';
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function setUserInfo(string $userInfo): self
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    public function getOauthToken(): OAuthToken
    {
        return $this->oauthToken;
    }

    public function setOauthToken(OAuthToken $oauthToken): self
    {
        $this->oauthToken = $oauthToken;

        return $this;
    }
}

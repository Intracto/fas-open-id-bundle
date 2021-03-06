<?php

namespace Intracto\FasOpenIdBundle\Security\Authentication\Token;

use Intracto\FasOpenIdBundle\Model\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

class FasOpenIdUserToken extends AbstractToken implements GuardTokenInterface
{
    /**
     * @var OAuthToken
     */
    private $oauthToken;

    public function __construct(UserInterface $user, OAuthToken $oauthToken, array $roles = [])
    {
        parent::__construct(['ROLE_USER']);

        $this->setUser($user);
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

    public function getOauthToken(): OAuthToken
    {
        return $this->oauthToken;
    }

    public function setOauthToken(OAuthToken $oauthToken): self
    {
        $this->oauthToken = $oauthToken;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [$this->oauthToken, parent::__serialize()];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
        [$this->oauthToken, $parentData] = $data;

        parent::__unserialize($parentData);
    }
}

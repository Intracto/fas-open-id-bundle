<?php

namespace Intracto\FasOpenIdBudle\Model;

use DateTime;

abstract class OAuthToken implements OAuthTokenInterface
{
    /**
     * @var array
     */
    protected $scope;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var DateTime
     */
    protected $expiresIn;

    /**
     * @var string
     */
    protected $idToken;

    /**
     * @inheritDoc
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    /**
     * @inheritDoc
     */
    public function setScope(array $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @inheritDoc
     */
    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @inheritDoc
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @inheritDoc
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresIn(): DateTime
    {
        return $this->expiresIn;
    }

    /**
     * @inheritDoc
     */
    public function setExpiresIn(DateTime $expiresIn): self
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @inheritDoc
     */
    public function getIdToken(): string
    {
        return $this->idToken;
    }

    /**
     * @inheritDoc
     */
    public function setIdToken(string $idToken): void
    {
        $this->idToken = $idToken;
    }
}

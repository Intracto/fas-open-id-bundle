<?php

/*
 * This file is part of the IntractoFasOpenIdBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Intracto\FasOpenIdBudle\Model;

use DateTime;

/**
 * @author Ruben Jacobs (ruben.jacobs@intracto.com)
 */
interface OAuthTokenInterface
{
    public function getScope(): array;
    public function setScope(array $scope): void;
    public function getAccessToken(): string;
    public function setAccessToken($accessToken): void;
    public function getRefreshToken(): string;
    public function setRefreshToken(string $refreshToken): void;
    public function getExpiresIn(): DateTime;
    public function setExpiresIn(DateTime $expiresIn): void;
    public function getIdToken(): string;
    public function setIdToken(string $idToken): void;
}

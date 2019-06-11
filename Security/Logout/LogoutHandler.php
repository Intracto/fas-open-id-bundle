<?php

namespace Intracto\FasOpenIdBundle\Security\Logout;

use Intracto\FasOpenIdBundle\Security\Authentication\Token\FasOpenIdUserToken;
use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var FasOpenIdOAuthClient
     */
    private $oauthClient;

    /**
     * LogoutHandler constructor.
     *
     * @param FasOpenIdOAuthClient $oauthClient
     */
    public function __construct(FasOpenIdOAuthClient $oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ($token instanceof FasOpenIdUserToken) {
            $this->oauthClient->logOut($token->getOauthToken()->getIdToken());
        }
    }
}

<?php

namespace Intracto\FasOpenIdBundle\Controller;

use Intracto\FasOpenIdBundle\Service\FasOpenIdOAuthClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends AbstractController
{
    /**
     * @var FasOpenIdOAuthClient
     */
    private $oauthClient;

    /**
     * AuthenticationController constructor.
     *
     * @param FasOpenIdOAuthClient $oauthClient
     */
    public function __construct(FasOpenIdOAuthClient $oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    public function login(string $acrValue): Response
    {
        return new RedirectResponse($this->oauthClient->getAuthenticationUrl($acrValue));
    }

    public function auth(Request $request): Response
    {
        if (!$request->query->has('code')) {
            throw $this->createAccessDeniedException();
        }

        return new Response();
    }
}

<?php

namespace Intracto\FasOpenIdBudle\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class FasOpenIdAuthenticationListener implements ListenerInterface
{
    public function handle(GetResponseEvent $event)
    {
    }
}

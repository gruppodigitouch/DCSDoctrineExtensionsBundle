<?php

namespace DCS\DoctrineExtensionsBundle\Listener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Gedmo\IpTraceable\IpTraceableListener;

class IpTraceableEventListener
{
    private $ipTraceableListener;
    private $request;

    public function __construct(IpTraceableListener $ipTraceableListener)
    {
        $this->ipTraceableListener = $ipTraceableListener;
    }

    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->request) {
            return;
        }

        $ip = $this->request->getClientIp();

        if (null !== $ip) {
            $this->ipTraceableListener->setIpValue($ip);
        }
    }
}

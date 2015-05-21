<?php

namespace HopitalNumerique\DomaineBundle\EventListener;

use HopitalNumerique\UserBundle\Event\UserEvent;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;

use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class GestionDomaineListener implements EventSubscriberInterface
{
    protected $_managerDomaine;

    public function __construct(DomaineManager $domaineManager)
    {
        $this->_managerDomaine = $domaineManager;
    }

    /**
     * Set the domaine from the request.
     *
     * This method should be attached to the kernel.request event.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if(is_null($session->get('domaineId'))
            || is_null($session->get('templateId')))
        {
            $domaine = $this->_managerDomaine->getDomaineFromHttpHost($request->server->get('HTTP_HOST'));
            $session->set('domaineId', $domaine->getId());
            $session->set('templateId', $domaine->getTemplate()->getId());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
<?php

namespace HopitalNumerique\CoreBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use HopitalNumerique\AutodiagBundle\Event\SynthesisEvent;
use HopitalNumerique\AutodiagBundle\Events;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class LogListener implements EventSubscriberInterface
{
    protected $container;

    /**
     * EntryListener constructor.
     *
     * @param Doctrine $doctrine
     */
    public function __construct(Doctrine $doctrine, Container $container, TokenStorage $security)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::SYNTHESIS_SHARED => 'addLogSynthesis',
            Events::SYNTHESIS_UNVALIDATED => 'addLogSynthesis',
            Events::SYNTHESIS_VALIDATED => 'addLogSynthesis',
        ];
    }

    public function addLogSynthesis(SynthesisEvent $event)
    {
        $user = $this->security->getToken()->getUser();

        $synthesis = $event->getSynthesis();

        if ($event->getName() == 'synthesis.validated') {
            $action = 'validate';
        } elseif ($event->getName() == 'synthesis.unvalidated') {
            $action = 'unvalidate';
        } elseif ($event->getName() == 'synthesis.shared') {
            $action = 'share';
        }

        $class = 'HopitalNumerique\AutodiagBundle\Entity\Synthesis';

        $this->container->get('hopitalnumerique_core.log')->Logger($action, $synthesis, $synthesis->getName(), $class, $user);
    }
}

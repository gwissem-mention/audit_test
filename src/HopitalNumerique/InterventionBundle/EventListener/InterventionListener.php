<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Transaction;
use AppBundle\Event\BottleEvent;
use AppBundle\Events;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TransactionSubscriber implements EventSubscriberInterface
{

    /**
     * EntryListener constructor.
     * @param Doctrine $doctrine
     */
    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::BOTTLE_CREATE => 'addTransaction',
            Events::BOTTLE_UPDATE => 'updateTransaction',
            Events::BOTTLE_LEAVE => 'leaveTransaction',
        );
    }

    public function addTransaction(BottleEvent $event)
    { //dump($event->getBottle());die;
        $transaction = new Transaction();
        $transaction->setType('E');
        $transaction->setBottle($event->getBottle());
        $transaction->setDestination($event->getBottle()->getLocation());
        $em = $this->doctrine->getEntityManager();
        $em->persist($transaction);
        $em->flush();
    }

    public function updateTransaction(BottleEvent $event)
    {
        dump('test');
    }

    public function leaveTransaction(BottleEvent $event)
    {
        dump('leave');
    }
}

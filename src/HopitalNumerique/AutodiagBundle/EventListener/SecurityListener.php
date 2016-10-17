<?php

namespace HopitalNumerique\AutodiagBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Service\AutodiagEntrySession;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SecurityListener
{
    public function __construct(TokenStorage $security, AutodiagEntrySession $autodiagEntrySession, Doctrine $doctrine)
    {
        $this->security = $security;
        $this->autodiagEntrySession = $autodiagEntrySession;
        $this->doctrine = $doctrine;
    }

    public function onSecurityInteractiveLogin()
    {
        // Si la session ne contient pas d'entry on sort de l'event
        if (!$this->autodiagEntrySession->exist()) {
            return;
        }

        $user = $this->security->getToken()->getUser();

        $entries = $this->autodiagEntrySession->getAll();

        // Pour chaque entry stockée en session, si elle ne possède pas déjà un utilisateur
        // on lui assigne l'utilisateur qui vient de se connecter et on retire l'entry de la session
        /** @var AutodiagEntry $entry */
        foreach ($entries as $entry) {
            if ($user && null === $entry->getUser()) {
                $manager = $this->doctrine->getManager();
                $entry->setUser($user);
                $entry->getSynthesis()->setUser($user);
                $manager->persist($entry);
                $manager->flush();

                $this->autodiagEntrySession->remove($entry);
            }
        }
    }
}

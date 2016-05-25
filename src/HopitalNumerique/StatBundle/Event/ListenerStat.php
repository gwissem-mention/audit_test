<?php
namespace HopitalNumerique\StatBundle\Event;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Event\ObjetEvent;
use HopitalNumerique\StatBundle\Manager\StatTelechargementManager;
use HopitalNumerique\StatBundle\Entity\StatTelechargement;
use Symfony\Component\EventDispatcher\GenericEvent;

class ListenerStat
{
    private $statTelechargementManager;

    public function __construct(StatTelechargementManager $statTelechargementManager)
    {
        $this->StatTelechargementManager = $statTelechargementManager;
    }

    public function objetDownloadSuccess(ObjetEvent $event)
    {
        $objet = $event->getObjet();
        $user = $event->getUser();
        $type = $event->getType();

        if ($objet instanceof Objet) {
            $statTelechargement = new StatTelechargement();
            $statTelechargement->setObjet($objet);
            $statTelechargement->setDateNow();
            $statTelechargement->setFileName((1 == $type) ? $objet->getPath() : $objet->getPath2());
            // Si l'utilisateur est connecté alors on l'ajoute, sinon on récupére le session_id
            if (is_null($user)) {
                $statTelechargement->setSessionId(session_id());
            } else {
                $statTelechargement->setUser($user);
            }
            // Sauvegarde de l'entié StatTelechargement
            $this->StatTelechargementManager->save($statTelechargement);
        }
    }
}

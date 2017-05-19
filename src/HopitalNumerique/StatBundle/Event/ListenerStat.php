<?php
namespace HopitalNumerique\StatBundle\Event;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Event\ObjetEvent;
use HopitalNumerique\StatBundle\Manager\StatTelechargementManager;
use HopitalNumerique\StatBundle\Entity\StatTelechargement;

/**
 * Class ListenerStat
 */
class ListenerStat
{
    private $statTelechargementManager;

    /**
     * ListenerStat constructor.
     *
     * @param StatTelechargementManager $statTelechargementManager
     */
    public function __construct(StatTelechargementManager $statTelechargementManager)
    {
        $this->statTelechargementManager = $statTelechargementManager;
    }

    /**
     * @param ObjetEvent $event
     */
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

            if (is_null($user)) {
                $statTelechargement->setSessionId(session_id());
            } else {
                $statTelechargement->setUser($user);
            }

            $this->statTelechargementManager->save($statTelechargement);
        }
    }
}

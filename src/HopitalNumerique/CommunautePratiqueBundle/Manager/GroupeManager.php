<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager de Document.
 * @method GroupeRepository getRepository()
 */
class GroupeManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe';

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * GroupeManager constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($em);
    }

    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarresByUser(Domaine $domaine = null, User $user)
    {
        return $this->getRepository()->findNonDemarres($domaine, $user, true, true);
    }

    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param User|null $user
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarres(Domaine $domaine = null, User $user = null)
    {
        return $this->getRepository()->findNonDemarres($domaine, $user, true);
    }

    /**
     * Retourne les groupes en cours.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param User|null $user
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCours(Domaine $domaine = null, User $user = null)
    {
        return $this->getRepository()->findEnCours($domaine, $user, true);
    }

    /**
     * Retourne les groupes terminés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findTermines(Domaine $domaine = null)
    {
        return $this->getRepository()->findTermines($domaine, null, true);
    }

    /**
     * Retourne les groupes en cours.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCoursByUser(Domaine $domaine = null, User $user)
    {
        return $this->getRepository()->findEnCours($domaine, $user, true, true);
    }

    /**
     * Retourne les groupes terminés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findTerminesByUser(Domaine $domaine = null, User $user)
    {
        return $this->getRepository()->findTermines($domaine, $user, true);
    }

    /**
     * Retourne les groupes non fermés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine   Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user      Utilisateur
     * @param bool                                           $checkRegistration
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non fermés
     */
    public function findNonFermes(Domaine $domaine = null, User $user = null, $checkRegistration)
    {
        return $this->getRepository()->findNonFermes($domaine, $user, false, true, true, $checkRegistration);
    }

    /**
     * Retourne la QueryBuilder des groupes ayant des publications.
     *
     * @param Domaine|null $domain
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function findWithPublications(Domaine $domain = null)
    {
        return $this->getRepository()->findWithPublicationsQueryBuilder($domain, true)->getQuery()->getResult();
    }

    /**
     * Retourne les données pour le grid.
     *
     * @return array Données
     */
    public function getGridData(\StdClass $filtre)
    {
        return $this->getRepository()->getGridData($filtre->value['domaines']);
    }

    /**
     * @param Groupe $entity
     */
    public function save($entity)
    {
        parent::save($entity);

        if ($entity->getActif() && $entity->isNew()) {
            /**
             * Fire 'GROUP_CREATED' event if group is active
             */
            //$event = new GroupEvent($entity);
            //$this->eventDispatcher->dispatch(Events::GROUP_CREATED, $event);
            $entity->setIsNew(false);

            parent::save($entity);
        }
    }
}

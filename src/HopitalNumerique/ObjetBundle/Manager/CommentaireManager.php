<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\Commentaire;
use HopitalNumerique\ObjetBundle\Events;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use HopitalNumerique\ObjetBundle\Event\PublicationCommentedEvent;

/**
 * Manager de l'entité Commentaire.
 */
class CommentaireManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Commentaire';

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * CommentaireManager constructor.
     *
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($em);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau.
     *
     * @param \StdClass $condition
     *
     * @return array
     */
    public function getDatasForGrid(\StdClass $condition = null)
    {
        $commentaires = [];

        $results = $this->getRepository()->getDatasForGrid($condition)->getQuery()->getResult();

        foreach ($results as $key => $result) {
            $commentaires[$result['id']] = $result;

            // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
            //Récupération du prénom
            $prenom = strtolower($result['userPrenom']);
            //Découpage du prénom sur le tiret
            $tempsPrenom = explode('-', $prenom);
            //Unsset de la variable
            $prenom = '';
            //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
            foreach ($tempsPrenom as $key => $tempPrenom) {
                $prenom .= ('' !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
            }

            // ----Mise en majuscule du nom
            $nom = strtoupper($result['userNom']);

            //Suppression du nom et prenom
            unset($commentaires[$result['id']]['userNom']);
            unset($commentaires[$result['id']]['userPrenom']);

            //Ajout de la colonne "Prenom NOM"
            $commentaires[$result['id']]['nomPrenom'] = $prenom . ' ' . $nom;
        }

        return array_values($commentaires);
    }

    /**
     * Passe l'ensemble des commentaires à publier.
     *
     * @param array $commentaires Liste des commentaires
     */
    public function publierEtatCommentaire($commentaires)
    {
        foreach ($commentaires as $commentaire) {
            $commentaire->setPublier(true);
            $this->em->persist($commentaire);
        }

        $this->em->flush();
    }

    /**
     * Passe l'ensemble des commentaires à dépublier.
     *
     * @param array $commentaires Liste des commentaires
     */
    public function depublierEtatCommentaire($commentaires)
    {
        foreach ($commentaires as $commentaire) {
            $commentaire->setPublier(false);
            $this->em->persist($commentaire);
        }

        //save
        $this->em->flush();
    }

    /**
     * @param $entity
     */
    public function save($entity)
    {
        /** @var Commentaire $entity */
        parent::save($entity);

        /**
         * Fire 'PUBLICATION_COMMENTED' event
         */
        $event = new PublicationCommentedEvent($entity);
        $this->eventDispatcher->dispatch(Events::PUBLICATION_COMMENTED, $event);
    }
}

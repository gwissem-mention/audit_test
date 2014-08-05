<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class MaitriseUserManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser';

    /**
     * Met à jour l'ordre du détails
     *
     * @param User $user L'utilisateur auquel appartient les notes
     *
     * @return array(HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser)
     */
    public function getAllOrderedByPointDurForParcoursEtape( $user, $idParcoursEtape )
    {
        $notesByPointDur = array();
        $notes = $this->findBy(array('user' => $user, 'rechercheParcoursDetails' => $idParcoursEtape));

        foreach ($notes as $note)
        {
            $notesByPointDur[$note->getObjet()->getId()] = $note;
        }

        return $notesByPointDur;
    }

    /**
     * Retourne la moyenne des notes pour les étapes passées en param
     *
     * @param RechercheParcours $rechercheParcours
     *
     * @return array Tableau des moyenne triées par étape
     */
    public function getAverage( $rechercheParcours )
    {
        $etapesId = $rechercheParcours->getRecherchesParcoursDetailsIds();

        //Récupération du tableau des objets maitrisés
        $objetsMaitrises = $this->getRepository()->getAverage( $etapesId )->getQuery()->getResult();

        $moyennes = array();
        //Cast des moyenne en int arrondi à l'entier
        foreach ($objetsMaitrises as $key => $etape) 
        {
            $moyennes[$etape['etapeId']] = intval($etape['moyenne'], 0);
        }

        return $moyennes;
    }
}
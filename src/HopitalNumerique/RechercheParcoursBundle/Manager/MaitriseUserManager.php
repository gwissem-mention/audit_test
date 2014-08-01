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
    public function getAllOrderedByPointDur( $user )
    {
        $notesByPointDur = array();
        $notes = $this->findAll(array('user' => $user));

        foreach ($notes as $note)
        {
            $notesByPointDur[$note->getObjet()->getId()] = $note;
        }

        return $notesByPointDur;
    }
}
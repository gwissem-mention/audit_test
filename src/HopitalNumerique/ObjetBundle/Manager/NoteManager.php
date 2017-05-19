<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Note.
 */
class NoteManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Note';

    /**
     * Retourne la note moyenne d'un objet.
     *
     * @param int $idObjet Identifiant de l'objet ou contenu passé en param
     *
     * @return float valeur arrondi de la moyenne des notes de l'objet passé en param
     */
    public function getMoyenneNoteByObjet($idObjet, $isContenu)
    {
        return $this->getRepository()->getMoyenneNoteByObjet($idObjet, $isContenu)->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $idObjet
     * @param $isContenu
     *
     * @return mixed
     */
    public function countNbNoteByObjet($idObjet, $isContenu)
    {
        return $this->getRepository()->countNbNoteByObjet($idObjet, $isContenu)->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $objectId
     * @param $isContenu
     *
     * @return array
     */
    public function countReviewByMark($objectId, $isContenu)
    {
        return $this->getRepository()->countReviewByMark($objectId, $isContenu)->getQuery()->getResult();
    }

    /**
     * @param $idDomaine
     *
     * @return mixed
     */
    public function findNoteByDomaine($idDomaine)
    {
        return $this->getRepository()->findNoteByDomaine($idDomaine)->getQuery()->getResult();
    }
}

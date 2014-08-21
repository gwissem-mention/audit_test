<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Manager de l'entité Note.
 */
class NoteManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Note';


    /**
     * Retourne la note moyenne d'un objet
     *
     * @param nt $idObjet Identifiant de l'objet ou contenu passé en param
     *
     * @return Double valeur arrondi de la moyenne des notes de l'objet passé en param
     */
    public function getMoyenneNoteByObjet( $idObjet, $isContenu )
    {
        return $this->getRepository()->getMoyenneNoteByObjet( $idObjet, $isContenu )->getQuery()->getSingleScalarResult();
    }

    public function countNbNoteByObjet( $idObjet, $isContenu )
    {
        return $this->getRepository()->countNbNoteByObjet( $idObjet, $isContenu )->getQuery()->getSingleScalarResult();
    }
}
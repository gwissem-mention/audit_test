<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class ExpBesoinManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\ExpBesoin';

    public function countQuestions()
    {
        return $this->getRepository()->countQuestions()->getQuery()->getSingleScalarResult();
    }

    /**
     * Met à jour l'ordre des questions
     *
     * @param array  $elements Les éléments
     * @param Object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder( $elements )
    {
        $order = 1;

        foreach($elements as $element) 
        {
            $question = $this->findOneBy( array('id' => $element['id']) );
            $question->setOrder( $order );
            $order++;
        }
    }

}
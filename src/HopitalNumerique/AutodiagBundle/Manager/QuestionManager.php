<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Question.
 */
class QuestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Question';

    /**
     * Compte le nombre de questions lié au chapitre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function countQuestions( $chapitre )
    {
        return $this->getRepository()->countQuestions($chapitre)->getQuery()->getSingleScalarResult();
    }

}

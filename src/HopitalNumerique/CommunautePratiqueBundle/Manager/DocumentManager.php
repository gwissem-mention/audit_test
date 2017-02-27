<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

/**
 * Manager de Document.
 */
class DocumentManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Document';

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = ['libelle' => 'ASC'];
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}

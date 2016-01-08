<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

/**
 * Manager de Document.
 */
class DocumentManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Document';


    /**
     * @inheritDoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = array('libelle' => 'ASC');
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}

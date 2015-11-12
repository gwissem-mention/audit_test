<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

class GroupeManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe';
    
    /**
     * Retourne les données pour le grid.
     *
     * @return array Données
     */
    public function getGridData(\StdClass $filtre)
    {
        return $this->getRepository()->getGridData($filtre->value['domaines']);
    }
}

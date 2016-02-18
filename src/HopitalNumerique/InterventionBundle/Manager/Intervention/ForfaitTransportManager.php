<?php
namespace HopitalNumerique\InterventionBundle\Manager\Intervention;

use Nodevo\ToolsBundle\Manager\Manager;

/**
 * Manager de ForfaitInterventionTransport.
 */
class ForfaitTransportManager extends Manager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\Intervention\ForfaitTransport';


    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->findBy(array(), array('distanceMaximum' => 'ASC'));
    }

    /**
     * Retourne le ForfaitTransport d'une distance en km.
     *
     * @param integer $distance Distance
     * @return \HopitalNumerique\InterventionBundle\Entity\Intervention\ForfaitTransport|null ForfaitTransport correspondant
     */
    public function getForDistance($distance)
    {
        return $this->getRepository()->getForDistance($distance);
    }
}

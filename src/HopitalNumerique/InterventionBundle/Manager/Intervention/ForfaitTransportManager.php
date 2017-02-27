<?php

namespace HopitalNumerique\InterventionBundle\Manager\Intervention;

use Nodevo\ToolsBundle\Manager\Manager;

/**
 * Manager de ForfaitInterventionTransport.
 */
class ForfaitTransportManager extends Manager
{
    protected $class = 'HopitalNumerique\InterventionBundle\Entity\Intervention\ForfaitTransport';

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->findBy([], ['distanceMaximum' => 'ASC']);
    }

    /**
     * Retourne le ForfaitTransport d'une distance en km.
     *
     * @param int $distance Distance
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\Intervention\ForfaitTransport|null ForfaitTransport correspondant
     */
    public function getForDistance($distance)
    {
        return $this->getRepository()->getForDistance($distance);
    }
}

<?php

namespace HopitalNumerique\CartBundle\Model;

use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

interface DomainInterface
{
    /**
     * @return PersistentCollection|Domaine[]
     */
    function getDomains();
}

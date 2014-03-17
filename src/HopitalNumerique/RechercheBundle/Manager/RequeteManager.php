<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Manager de l'entité Requete.
 */
class RequeteManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\Requete';

}
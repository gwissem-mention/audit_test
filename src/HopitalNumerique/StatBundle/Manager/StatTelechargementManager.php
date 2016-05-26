<?php

namespace HopitalNumerique\StatBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class StatTelechargementManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\StatBundle\Entity\StatTelechargement';
    protected $_securityContext;

    /**
     * @param EntityManager    $em               [description]
     * @param SecurityContext $securityContext Security Context
     */
    public function __construct($em, $securityContext)
    {
        parent::__construct($em);
        $this->_securityContext = $securityContext;
    }
}

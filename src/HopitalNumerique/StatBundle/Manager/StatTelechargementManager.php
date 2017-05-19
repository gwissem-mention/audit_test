<?php

namespace HopitalNumerique\StatBundle\Manager;

use Doctrine\ORM\EntityManager;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Manager de l'entité Requete.
 */
class StatTelechargementManager extends BaseManager
{
    protected $class = 'HopitalNumerique\StatBundle\Entity\StatTelechargement';

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * StatTelechargementManager constructor.
     *
     * @param EntityManager   $em
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $em, SecurityContext $securityContext)
    {
        parent::__construct($em);

        $this->securityContext = $securityContext;
    }
}

<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Grid;

/**
 * Grid de Groupe.
 */
class GroupeGrid extends \Nodevo\GridBundle\Grid\Grid
{
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;
    
    
    /**
     * {@inheritDoc}
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->user = $container->get('security.context')->getToken()->getUser();
        if (!($this->user instanceof HopitalNumerique\UserBundle\Entity\User))
        {
            throw new \Exception('Aucun utilisateur connecté.');
        }
    }
    
    
    /**
     * {@inheritDoc}
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_communautepratique.manager.groupe');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
    }
}

<?php
/**
 * Manager pour les demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager pour les demandes d'intervention.
 */
class InterventionDemandeManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     */
    private $securityContext;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext)
    {
        parent::__construct($entityManager);
        $this->securityContext = $securityContext;
    }

    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention.
     * 
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonnees_DemandesNouvelles()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesNouvelles($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées.
     * 
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonnees_DemandesTraitees()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesTraitees($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
}

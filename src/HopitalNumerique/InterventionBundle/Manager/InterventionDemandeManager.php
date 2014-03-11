<?php
/**
 * Manager pour les demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager pour les demandes d'intervention.
 */
class InterventionDemandeManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        parent::__construct($entityManager);
        $this->container = $container;
    }

    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention.
     * 
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonnees_DemandesNouvelles()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesNouvelles();

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées.
     * 
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonnees_DemandesTraitees()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesTraitees();

        return $interventionDemandes;
    }
}

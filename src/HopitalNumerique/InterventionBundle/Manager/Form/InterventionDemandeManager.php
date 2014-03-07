<?php
/**
 * Manager pour le formulaire des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manager pour le formulaire des demandes d'intervention.
 */
class InterventionDemandeManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;
    
    /**
     * Constructeur du manager gérant les formulaires de demandes d'intervention.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Retourne la liste des civilités pour les listes de formulaire.
     *
     * @return array Liste des civilités pour les listes de formulaire
     */
    public function getInterventionTypesChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'TYPE_INTERVENTION'));
    }
}
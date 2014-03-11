<?php
/**
 * Manager pour les états d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;

/**
 * Manager pour les états d'intervention.
 */
class InterventionEtatManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;

    /**
     * Constructeur du manager gérant les états d'intervention.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retourne l'état d'intervention correspondant à Demande initiale.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Demande initiale
     */
    public function getInterventionEtatDemandeInitiale()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatDemandeInitialeId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Mise en attente par le CMSI.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Mise en attente par le CMSI
     */
    public function getInterventionEtatAttenteCmsi()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatAttenteCmsiId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Refusé par le CMSI.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Refusé par le CMSI
     */
    public function getInterventionEtatRefusCmsiInitiale()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatRefusCmsiId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Accepté par le CMSI.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Accepté par le CMSI
     */
    public function getInterventionEtatAcceptationCmsi()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatAcceptationCmsiId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Accepté par le CMSI - 1ère relance ambassadeur.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Accepté par le CMSI - 1ère relance ambassadeur
     */
    public function getInterventionEtatAcceptationCmsiRelance1()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatAcceptationCmsiRelance1Id());
    }
    /**
     * Retourne l'état d'intervention correspondant à Accepté par le CMSI - 2nde relance ambassadeur.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Accepté par le CMSI - 2nde relance ambassadeur
     */
    public function getInterventionEtatAcceptationCmsiRelance2()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatAcceptationCmsiRelance2Id());
    }
    /**
     * Retourne l'état d'intervention correspondant à Refusé par l'ambassadeur.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Refusé par l'ambassadeur
     */
    public function getInterventionEtatRefusAmbassadeurInitiale()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatRefusAmbassadeurId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Accepté par l'ambassadeur.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Accepté par l'ambassadeur
     */
    public function getInterventionEtatAcceptationAmbassadeur()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatAcceptationAmbassadeurId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Terminée.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Terminée
     */
    public function getInterventionEtatTermine()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatTermineId());
    }
    /**
     * Retourne l'état d'intervention correspondant à Clôturée.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'intervention correspondant à Clôturée
     */
    public function getInterventionEtatCloture()
    {
        return $this->findOneById(InterventionEtat::getInterventionEtatClotureId());
    }

    /**
     * Récupère un état d'intervention par rapport à son référence ID.
     * 
     * @param integer $id ID de la référence correspondant à l'état d'intervention
     */
    private function findOneById($referenceid)
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findOneById($referenceid);
    }
}

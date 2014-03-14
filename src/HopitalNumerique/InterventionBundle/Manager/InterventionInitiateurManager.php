<?php
/**
 * Manager pour les initiateurs des interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager pour les initiateurs des interventions.
 */
class InterventionInitiateurManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur';

    /**
     * Returne le CMSI.
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur Le CMSI
     */
    public function getInterventionInitiateurCmsi()
    {
        return $this->getRepository()->findOneById(InterventionInitiateur::getInterventionInitiateurCmsiId());
    }
    /**
     * Returne l'établissement.
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur L'établissement
     */
    public function getInterventionInitiateurEtablissement()
    {
        return $this->getRepository()->findOneById(InterventionInitiateur::getInterventionInitiateurEtablissementId());
    }
}

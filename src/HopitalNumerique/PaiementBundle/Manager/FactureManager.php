<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Facture.
 */
class FactureManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\PaiementBundle\Entity\Facture';
    protected $_interventionManager;
    protected $_referenceManager;

    /**
     * Contruit le manager
     *
     * @param EntityManager              $em                  L'Entity Manager
     */
    public function __construct(EntityManager $em, array $managers) // InterventionDemandeManager $interventionManager, ReferenceManager $referenceManager,  )
    {
        parent::__construct($em);

        $this->_interventionManager = $managers[0];
        $this->_referenceManager    = $managers[1];
    }

    /**
     * Créer l'objet facture pour l'user connecté avec la liste d'interventions/formations sélectionnées
     *
     * @param User  $user          L'utilisateur connecté
     * @param array $interventions Liste des interventions sélectionnées
     * @param array $formations    Liste des formations sélectionnées
     *
     * @return Facture
     */
    public function createFacture($user, $interventions, $formations)
    {
        //create object facture
        $facture = $this->createEmpty();
        $facture->setUser( $user );
        $this->save($facture);

        //prepare ref
        $statutRemboursement = $this->_referenceManager->findOneBy(array('id'=>6));

        //handle interventions
        foreach($interventions as $id => $prix) {
            $intervention = $this->_interventionManager->findOneBy( array('id' => $id) );
            $intervention->setFacture( $facture );
            $intervention->setRemboursementEtat( $statutRemboursement );
            $intervention->setTotal( $prix );
            
            $facture->addIntervention( $intervention );
        }
        $this->save($facture);

        //handle formations
        $formationsEntities = array();

        return $facture;
    }
}
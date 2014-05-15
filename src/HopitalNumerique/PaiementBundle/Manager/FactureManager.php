<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\Manager\InterventionDemandeManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

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
     * @param EntityManager $em L'Entity Manager
     */
    public function __construct(EntityManager $em, array $managers)
    {
        parent::__construct($em);

        $this->_interventionManager = $managers[0];
        $this->_referenceManager    = $managers[1];
        $this->_formationManager    = $managers[2];
    }

    /**
     * Créer l'objet facture pour l'user connecté avec la liste d'interventions/formations sélectionnées
     *
     * @param User    $user          L'utilisateur connecté
     * @param array   $interventions Liste des interventions sélectionnées
     * @param array   $formations    Liste des formations sélectionnées
     * @param integer $supplement    Supplement de la région
     *
     * @return Facture
     */
    public function createFacture($user, $interventions, $formations, $supplement)
    {
        //create object facture
        $facture = $this->createEmpty();
        $facture->setUser( $user );
        $this->save($facture);

        //prepare ref
        $statutRemboursement = $this->_referenceManager->findOneBy(array('id'=>6));

        //make total
        $total = 0;

        //handle interventions
        if( $interventions ) {
            foreach($interventions as $id => $prix) {
                $intervention = $this->_interventionManager->findOneBy( array('id' => $id) );
                $intervention->setFacture( $facture );
                $intervention->setRemboursementEtat( $statutRemboursement );
                $intervention->setTotal( $prix );
                
                $facture->addIntervention( $intervention );

                $total += $prix;
            }
        }

        //handle formations
        if( $formations ){
            foreach ($formations as $id => $prix) {
                $formation = $this->_formationManager->findOneBy( array('id' => $id) );
                $formation->setFacture( $facture );
                $formation->setEtatRemboursement( $statutRemboursement );
                $formation->setTotal( $prix );
                $formation->setSupplement( $supplement );
                
                $facture->addFormation( $formation );

                $total += $prix;
            }
        }

        $facture->setTotal( $total );
        $this->save($facture);
        
        return $facture;
    }

    /**
     * Formate les réponses aux question du questionnaire ambassadeur
     *
     * @param array $reponses Les réponses
     *
     * @return array
     */
    public function formateInfos( $reponses )
    {
        $infos = array('telDirecteur' => '', 'libelleContact' => '', 'nomContact' => '');

        foreach($reponses as $reponse){
            switch ($reponse->getQuestion()->getId()) {
                case 36:
                    $infos['telDirecteur'] = $reponse->getReponse();
                    break;
                case 37:
                    $infos['libelleContact'] = $reponse->getReponse();
                    break;
                case 38:
                    $infos['nomContact'] = $reponse->getReponse();
                    break;
                
                default:
                    break;
            }
        }

        return $infos;
    }

    /**
     * Passe les interventions de la facture au statut payé
     *
     * @param Facture $facture La facture
     *
     * @return empty
     */
    public function paye( $facture )
    {
        $statutRemboursement = $this->_referenceManager->findOneBy( array( 'id' => 7 ) );
        $interventions       = $facture->getInterventions()->toArray();

        //change interventions state
        foreach ($interventions as &$intervention)
            $intervention->setRemboursementEtat( $statutRemboursement );
        
        //change facture state
        $facture->setPayee( true );

        //save factue = implicit save interventions
        $this->save( $facture );
    }

    /**
     * Retourne la liste des factures ordonnées par date
     *
     * @return array
     */
    public function getFacturesOrdered( $user )
    {
        return $this->getRepository()->getFacturesOrdered( $user )->getQuery()->getResult();
    }
}
<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\PaiementBundle\Manager\FactureAnnuleeManager;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\PaiementBundle\Entity\Facture;

/**
 * Manager de l'entité Facture.
 */
class FactureManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\PaiementBundle\Entity\Facture';
    protected $_interventionManager;
    protected $_referenceManager;

    /**
     * @var \HopitalNumerique\PaiementBundle\Manager\FactureAnnuleeManager FactureAnnuleeManager
     */
    private $factureAnnuleeManager;


    /**
     * Contruit le manager
     *
     * @param EntityManager $em L'Entity Manager
     */
    public function __construct(EntityManager $em, array $managers, FactureAnnuleeManager $factureAnnuleeManager)
    {
        parent::__construct($em);

        $this->_interventionManager = $managers[0];
        $this->_referenceManager    = $managers[1];
        $this->_formationManager    = $managers[2];
        $this->factureAnnuleeManager = $factureAnnuleeManager;
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

        if(!is_null($supplement))
        {
            //handle formations
            if( $formations ){
                foreach ($formations as $id => $prixSupplement) {

                    list($prix, $hasSupplement) = explode("_", $prixSupplement);

                    $formation = $this->_formationManager->findOneBy( array('id' => $id) );
                    $formation->setFacture( $facture );
                    $formation->setEtatRemboursement( $statutRemboursement );
                    $formation->setTotal( $prix);
                    $formation->setSupplement( $hasSupplement == 'supp' ? $supplement : 0 );
                    
                    $facture->addFormation( $formation );

                    $total += $prix;
                }
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
        $facture->setDatePaiement( new \DateTime() );

        //save facture => implicit save interventions
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

    /**
     * Retourne si la facture peut être générée.
     *
     * @param array<\HopitalNumerique\InterventionBundle\Entity\InterventionDemande> $interventionDemandes Demandes d'intervention
     * @return boolean Vrai si la facture peut être générée
     */
    public function canGenererFacture(array $interventionDemandes)
    {
        foreach ($interventionDemandes as $interventionDemande) {
            if (null !== $interventionDemande->getReferent() && null === $interventionDemande->getReferent()->getEtablissementRattachementSante()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Annule la facture.
     *
     * @param \HopitalNumerique\PaiementBundle\Entity\Facture $facture Facture
     */
    public function cancel(Facture $facture)
    {
        $factureAnnulee = $this->factureAnnuleeManager->createByFacture($facture);
        $this->save($factureAnnulee);

        $facture->removeInterventions();
        $facture->removeFormations();
        $this->save($facture);
    }
}

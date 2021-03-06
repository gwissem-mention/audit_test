<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\PaiementBundle\Entity\Facture;

/**
 * Manager de l'entité Facture.
 */
class FactureManager extends BaseManager
{
    protected $class = 'HopitalNumerique\PaiementBundle\Entity\Facture';
    protected $_interventionManager;
    protected $_referenceManager;

    /**
     * @var \HopitalNumerique\PaiementBundle\Manager\FactureAnnuleeManager FactureAnnuleeManager
     */
    private $factureAnnuleeManager;

    /**
     * Contruit le manager.
     *
     * @param EntityManager $em L'Entity Manager
     */
    public function __construct(EntityManager $em, array $managers, FactureAnnuleeManager $factureAnnuleeManager)
    {
        parent::__construct($em);

        $this->_interventionManager = $managers[0];
        $this->_referenceManager = $managers[1];
        $this->_formationManager = $managers[2];
        $this->factureAnnuleeManager = $factureAnnuleeManager;
    }

    /**
     * @param array $paymentIds
     * @param $charset
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCsvExport(array $paymentIds, $charset)
    {
        $payments = $this->findBy(['id' => $paymentIds]);

        $cols = [
            'Numéro de facture',
            'Date',
            'Nom',
            'Prénom',
            'E-mail',
            'Région',
            'Etablissement',
            'Total',
            'Payée',
            'Annulée',
        ];
        $datas = [];

        /** @var Facture $payment */
        foreach ($payments as $payment) {
            $datas[] = [
                $payment->getId(),
                $payment->getDateCreation()->format('d/m/Y'),
                $payment->getUser()->getLastname(),
                $payment->getUser()->getFirstname(),
                $payment->getUser()->getEmail(),
                $payment->getUser()->getRegion()->getLibelle(),
                $payment->getUser()->getOrganizationString(),
                $payment->getTotal(),
                $payment->isPayee() ? 'Payée' : 'Non payée',
                $payment->isAnnulee() ? 'Annulée' : '',
            ];
        }

        return $this->exportCsv(
            array_values($cols),
            $datas,
            'export-liste-factures.csv',
            $charset
        );
    }

    /**
     * Créer l'objet facture pour l'user connecté avec la liste d'interventions/formations sélectionnées.
     *
     * @param User  $user          L'utilisateur connecté
     * @param array $interventions Liste des interventions sélectionnées
     * @param array $formations    Liste des formations sélectionnées
     * @param int   $supplement    Supplement de la région
     *
     * @return Facture
     */
    public function createFacture($user, $interventions, $formations, $supplement)
    {
        //create object facture
        /** @var Facture $facture */
        $facture = $this->createEmpty();
        $facture->setUser($user);
        $this->save($facture);

        //prepare ref
        $statutRemboursement = $this->_referenceManager->findOneBy(['id' => 6]);

        //make total
        $total = 0;

        //handle interventions
        if ($interventions) {
            foreach ($interventions as $id => $prix) {
                /** @var InterventionDemande $intervention */
                $intervention = $this->_interventionManager->findOneBy(['id' => $id]);
                $intervention->setFacture($facture);
                $intervention->setRemboursementEtat($statutRemboursement);
                $intervention->setTotal($prix);

                $facture->addIntervention($intervention);

                $total += $prix;
            }
        }

        if (!is_null($supplement)) {
            //handle formations
            if ($formations) {
                foreach ($formations as $id => $prixSupplement) {
                    list($prix, $hasSupplement) = explode('_', $prixSupplement);

                    $formation = $this->_formationManager->findOneBy(['id' => $id]);
                    $formation->setFacture($facture);
                    $formation->setEtatRemboursement($statutRemboursement);
                    $formation->setTotal($prix);
                    $formation->setSupplement($hasSupplement == 'supp' ? $supplement : 0);

                    $facture->addFormation($formation);

                    $total += $prix;
                }
            }
        }

        $facture->setTotal($total);
        $this->save($facture);

        return $facture;
    }

    /**
     * Formate les réponses aux question du questionnaire ambassadeur.
     *
     * @param array $reponses Les réponses
     *
     * @return array
     */
    public function formateInfos($reponses)
    {
        $infos = ['telDirecteur' => '', 'libelleContact' => '', 'nomContact' => ''];

        foreach ($reponses as $reponse) {
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
     * Passe les interventions de la facture au statut payé.
     *
     * @param Facture $facture La facture
     */
    public function paye($facture)
    {
        $statutRemboursement = $this->_referenceManager->findOneBy(['id' => 7]);
        $interventions = $facture->getInterventions()->toArray();

        //change interventions state
        /** @var InterventionDemande $intervention */
        foreach ($interventions as &$intervention) {
            $intervention->setRemboursementEtat($statutRemboursement);
        }

        //change facture state
        $facture->setPayee(true);
        $facture->setDatePaiement(new \DateTime());

        //save facture => implicit save interventions
        $this->save($facture);
    }

    /**
     * Passe les interventions de la facture au statut annulée ou activée.
     *
     * @param Facture $facture La facture
     *
     * @return bool
     */
    public function changeEtat($facture)
    {
        if ($facture->isAnnulee()) {
            $facture->setAnnulee(false);
        } else {
            $facture->setAnnulee(true);
        }
        $this->save($facture);

        return $facture->isAnnulee();
    }

    /**
     * Retourne la liste des factures ordonnées par date.
     *
     * @return array
     */
    public function getFacturesOrdered($user, $onlyValid = true)
    {
        return $this->getRepository()->getFacturesOrdered($user, $onlyValid)->getQuery()->getResult();
    }

    /**
     * Retourne si la facture peut être générée.
     *
     * @param InterventionDemande[] $interventionDemandes
     *
     * @return bool
     */
    public function canGenererFacture(array $interventionDemandes)
    {
        // Problème de génération en attente de l'ANAP.
//        foreach ($interventionDemandes as $interventionDemande) {
//            if (null !== $interventionDemande->getReferent()) {
//                return false;
//            }
//        }

        return true;
    }

    /**
     * Annule la facture.
     *
     * @param Facture $facture Facture
     */
    public function cancel(Facture $facture)
    {
        $factureAnnulee = $this->factureAnnuleeManager->createByFacture($facture);
        $this->save($factureAnnulee);

        foreach ($facture->getInterventions() as $intervention) {
            $intervention->setRemboursementEtat(null);
        }

        foreach ($facture->getFormations() as $formation) {
            $formation->setEtatRemboursement(null);
        }

        $facture->removeInterventions();
        $facture->removeFormations();
        $this->save($facture);
    }
}

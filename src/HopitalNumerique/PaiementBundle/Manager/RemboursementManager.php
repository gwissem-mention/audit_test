<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\DependencyInjection\Intervention\ForfaitTransport as ForfaitTransportService;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\PaiementBundle\Entity\Remboursement;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Remboursement.
 */
class RemboursementManager extends BaseManager
{
    protected $class = 'HopitalNumerique\PaiementBundle\Entity\Remboursement';

    /**
     * @var ForfaitTransportService ForfaitTransportService
     */
    private $forfaitTransportService;

    /**
     * Constructeur.
     *
     * @param EntityManager           $em
     * @param ForfaitTransportService $forfaitTransportService
     */
    public function __construct(EntityManager $em, ForfaitTransportService $forfaitTransportService)
    {
        parent::__construct($em);
        $this->forfaitTransportService = $forfaitTransportService;
    }

    /**
     * Remet en forme les données pour le tableau de suivi des paiements.
     *
     * @param array $interventions Les interventions
     * @param array $formations    Les formations
     *
     * @return array
     */
    public function calculPrice($interventions, $formations)
    {
        //build Table Remboursement
        $prix = $this->getPrixRemboursements();

        //build Array for Table front
        $results = [];

        //Manage interventions
        foreach ($interventions as $intervention) {
            $row = new \StdClass();

            //build Referent + etablissement
            /** @var User $referent */
            $referent = $intervention->getReferent();
            $etablissement = $referent->getOrganization() ? $referent->getOrganization()->getNom()
                : $referent->getOrganizationLabel()
            ;

            if (null === $intervention->getReferent()->getOrganization()
                || null === $intervention->getAmbassadeur()->getOrganization()
            ) {
                $forfaitTransport = 0;
            } else {
                $forfaitTransport = $this->forfaitTransportService->getCoutForDistanceBetweenEtablissements(
                    $intervention->getReferent()->getOrganization(),
                    $intervention->getAmbassadeur()->getOrganization()
                );
            }

            //build objet
            $row->id = $intervention->getId();
            $row->date = $intervention->getDateCreation();
            $row->referent = $referent->getPrenomNom();
            $row->etab = $etablissement;
            $row->type = 'Intervention : ' . $intervention->getInterventionType()->getLibelle();
            $row->discr = 'intervention';

            //calcul total
            $ambassadeurRegion = (null === $intervention->getAmbassadeur()->getRegion() ? '0'
                : $intervention->getAmbassadeur()->getRegion()->getId())
            ;
            $row->total = ['prix' => $prix['interventions'][$ambassadeurRegion]['total'] + $forfaitTransport];

            $results[] = $row;
        }

        //Manage fomartions (inscriptions to sessions)
        /** @var Inscription|null $lastInscription */
        $lastInscription = null;

        /** @var Inscription $formation */
        foreach ($formations as $formation) {
            if (is_null($formation->getUser())
                || is_null($formation->getUser()->getRegion())
                || !array_key_exists($formation->getUser()->getRegion()->getId(), $prix['formations'])
            ) {
                continue;
            }

            $row = new \StdClass();

            //build objet
            $row->id = $formation->getId();
            $row->date = $formation->getSession()->getDateSession();
            $row->referent = '-';
            $row->etab = '-';
            $row->type = 'Module : ' . $formation->getSession()->getModule()->getTitre();
            $row->discr = 'formation';
            $row->total = [
                'prix' => $prix['formations'][$formation->getUser()->getRegion()->getId()],
                'hasSupplement' => true,
            ];

            if (is_null($lastInscription)) {
                /** @var Inscription $lastInscription */
                $lastInscription = $formation;
            } else {
                // Test si la date de la session courante et
                // celle de la session d'avant (s'il y en a une) sont consécutives.
                if (intval($formation->getSession()->getDateSession()->diff(
                    $lastInscription->getSession()->getDateSession()
                )->days) == 1
                    // Ou si il y a un écart de 2 jours mais que la session précedente à durée plus d'une journée
                || (intval(
                    $formation->getSession()->getDateSession()->diff(
                        $lastInscription->getSession()->getDateSession()
                    )->days
                ) == 2
                && $lastInscription->getSession()->getDuree()->getId() > 401)
                ) {
                    $row->total['prix']          = 140;
                    $row->total['hasSupplement'] = false;
                }
                $lastInscription = $formation;
            }

            //TODO : sortir le 140
            //Ajout de 140€ si la durée de la session est supérieur à 1jour (max 2 jour en base)
            if ($formation->getSession()->getDuree()->getId() > 401) {
                $row->total['prix'] += 140;
            }

            $results[] = $row;
        }

        return $results;
    }

    /**
     * Retorune un tableau contenant les prix des interventions / formations.
     *
     * @return array
     */
    private function getPrixRemboursements()
    {
        $remboursements = $this->findAll();
        $prix = [];

        /** @var Remboursement $remboursement */
        foreach ($remboursements as $remboursement) {
            $total = intval($remboursement->getRepas() + $remboursement->getGestion());

            $prix['interventions'][$remboursement->getRegion()->getId()]['total'] = $total;

            $prix['formations'][$remboursement->getRegion()->getId()] = is_null($remboursement->getSupplement()) ? null
                : intval($total + $remboursement->getSupplement())
            ;
        }

        return $prix;
    }

    /**
     * Retourne la liste des remboursements ordonnées par région.
     *
     * @return array
     */
    public function getRemboursementsOrdered()
    {
        return $this->getRepository()->getRemboursementsOrdered()->getQuery()->getResult();
    }
}

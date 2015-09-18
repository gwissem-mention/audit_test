<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Remboursement.
 */
class RemboursementManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\PaiementBundle\Entity\Remboursement';

    /**
     * Remet en forme les données pour le tableau de suivi des paiements
     *
     * @param array $interventions Les interventions
     * @param array $formations    Les formations
     *
     * @return array
     */
    public function calculPrice( $interventions, $formations )
    {
        //build Table Remboursement
        $prix = $this->getPrixRemboursements();

        //build Array for Table front
        $results = array();

        //Manage interventions
        foreach ($interventions as $intervention) {
            $row = new \StdClass;
            
            //build Referent + etablissement
            $referent      = $intervention->getReferent();
            $etablissement = $referent->getEtablissementRattachementSante() ? $referent->getEtablissementRattachementSante()->getNom() : $referent->getAutreStructureRattachementSante();

            //build objet
            $row->id       = $intervention->getId();
            $row->date     = $intervention->getDateCreation();
            $row->referent = $referent->getPrenomNom();
            $row->etab     = $etablissement;
            $row->type     = 'Intervention : ' . $intervention->getInterventionType()->getLibelle();
            $row->discr    = 'intervention';

            //calcul total
            $ambassadeurRegion = $intervention->getAmbassadeur()->getRegion()->getId();
            $referentRegion    = $referent->getRegion()->getId();
            $row->total        = array('prix' => $prix['interventions'][$ambassadeurRegion]['total']);
            if( $ambassadeurRegion != $referentRegion )
                $row->total['prix'] = intval($row->total['prix'] + $prix['interventions'][$referentRegion]['intervention']);
            
            $results[] = $row;
        }

        //Manage fomartions (inscriptions to sessions)
        $lastInscription = null;
        foreach ($formations as $formation) 
        {
            if(is_null($prix['formations'][$formation]) && is_null($prix['formations'][$formation->getUser()]) && is_null($prix['formations'][$formation->getUser()->getRegion()]) && is_null($prix['formations'][$formation->getUser()->getRegion()->getId()]))
            {
                continue;
            }

            $row = new \StdClass;

            //build objet
            $row->id       = $formation->getId();
            $row->date     = $formation->getSession()->getDateSession();
            $row->referent = '-';
            $row->etab     = '-';
            $row->type     = 'Module : ' . $formation->getSession()->getModule()->getTitre();
            $row->discr    = 'formation';
            $row->total    = array(
                'prix'          => $prix['formations'][$formation->getUser()->getRegion()->getId()],
                'hasSupplement' => true,
            );

            if(is_null($lastInscription))
            {
                $lastInscription = $formation;
            }
            else
            {
                //Test si la date de la session courante et celle de la session d'avant (s'il y en a une) sont consécutives.
                if(intval($formation->getSession()->getDateSession()->diff($lastInscription->getSession()->getDateSession())->days) == 1
                    // Ou si il y a un écart de 2 jours mais que la session précedente à durée plus d'une journée
                    || (intval($formation->getSession()->getDateSession()->diff($lastInscription->getSession()->getDateSession())->days) == 2 
                            && $lastInscription->getSession()->getDuree()->getId() > 401) )
                {
                    $row->total['prix']          = 140;
                    $row->total['hasSupplement'] = false;
                }
                $lastInscription = $formation;
            }

            //TODO : sortir le 140
            //Ajout de 140€ si la durée de la session est supérieur à 1jour (max 2 jour en base)
            if( $formation->getSession()->getDuree()->getId() > 401 )
            {
                $row->total['prix'] += 140;
            }
            
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Retorune un tableau contenant les prix des interventions / formations
     *
     * @return array
     */
    public function getPrixRemboursements()
    {
        $remboursements = $this->findAll();
        $prix           = array();
        foreach($remboursements as $remboursement) {
            $total = intval($remboursement->getIntervention() + $remboursement->getRepas() + $remboursement->getGestion());

            $prix['interventions'][ $remboursement->getRegion()->getId() ]['total']        = $total;
            $prix['interventions'][ $remboursement->getRegion()->getId() ]['intervention'] = $remboursement->getIntervention();
            $prix['formations'][ $remboursement->getRegion()->getId() ]                    = is_null($remboursement->getSupplement()) ? null : intval($total + $remboursement->getSupplement());
        }

        return $prix;
    }

    /**
     * Retourne la liste des remboursements ordonnées par région
     *
     * @return array
     */
    public function getRemboursementsOrdered()
    {
        return $this->getRepository()->getRemboursementsOrdered()->getQuery()->getResult();
    }
}
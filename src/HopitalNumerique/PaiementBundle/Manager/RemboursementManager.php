<?php

namespace HopitalNumerique\PaiementBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Remboursement.
 */
class RemboursementManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\PaiementBundle\Entity\Remboursement';

    public function calculPrice( $interventions, $formations )
    {
        //build Table Remboursement
        $remboursements = $this->findAll();
        $prix           = array();
        foreach($remboursements as $remboursement) {
            $total = intval($remboursement->getIntervention() + $remboursement->getRepas() + $remboursement->getGestion());

            $prix['interventions'][ $remboursement->getRegion()->getId() ]['total']        = $total;
            $prix['interventions'][ $remboursement->getRegion()->getId() ]['intervention'] = $remboursement->getIntervention();
            $prix['formations'][ $remboursement->getRegion()->getId() ]                    = intval($total + $remboursement->getSupplement());
        }

        //build Array for Table front
        $results = array();
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
            $row->total        = $prix['interventions'][$ambassadeurRegion]['total'];
            if( $ambassadeurRegion != $referentRegion )
                $row->total = intval($row->total + $prix['interventions'][$referentRegion]['intervention']);
            
            $results[] = $row;
        }

        //TODO : ADD formations
        foreach ($formations as $formation) {
            $row = new \StdClass;

            $row->id       = $formation->getId();
            $row->date     = '';
            $row->referent = '';
            $row->etab     = '';
            $row->type     = 'Formation : ';
            $row->discr    = 'formation';
            $row->total    = 0;
            
            $results[] = $row;
        }        

        return $results;
    }
}
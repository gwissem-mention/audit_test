<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Resultat.
 */
class ResultatManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $resultats = $this->findBy( array( $condition->field => $condition->value) );
        $results   = array();

        foreach($resultats as $resultat)
        {
            $datas               = array();
            $datas['id']         = $resultat->getId();
            $datas['taux']       = $resultat->getTauxRemplissage() . '%';
            $datas['lastSave']   = $resultat->getDateLastSave();
            $datas['validation'] = $resultat->getDateValidation();

            if( $user = $resultat->getUser() )
            {
                $datas['user']          = $user->getPrenomNom();
                $datas['etablissement'] = $user->getEtablissementRattachementSante() ? $user->getEtablissementRattachementSante()->getNom() : $user->getAutreStructureRattachementSante();
            }else{
                $datas['user']          = '';
                $datas['etablissement'] = '';
            }

            $results[] = $datas;
        }

        return $results;
    }
}
<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\ResultatManager as ResultatManagerAutodiag;
use Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\AutodiagBundle\Manager\OutilManager;

/**
 * Manager de l'entité Categorie.
 */
class ImportExcelResultatManager extends ResultatManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';
    protected $_referenceManager;
    protected $_userManager;

    public function __construct($entityManager, OutilManager $outilManager, ReferenceManager $referenceManager, UserManager $userManager )
    {
        parent::__construct($entityManager, $outilManager);
        $this->_referenceManager = $referenceManager;
        $this->_userManager      = $userManager;
    }

    /**
     * Récupération des données des résultats du fichier excel d'import
     *
     * @param Array $arrayResultat  Tableau des données de résultats à persist
     * @param Array $arraySyntheses Tableau des données de syntheses à persist
     *
     * @return void
     */
    public function saveResultatImported( $arrayResultat, $arraySyntheses, $outil )
    {
        $arrayIdResultats = array();

        foreach ($arrayResultat as $key => $resultatDonnees) 
        {
            $resultat = $this->createEmpty();

            $resultat->setName($resultatDonnees['nom']);
            $resultat->setDateLastSave($resultatDonnees['dateDerniereSauvegarde']);
            $resultat->setDateValidation($resultatDonnees['dateValidation']);
            $resultat->setDateCreation($resultatDonnees['dateCreation']);
            $resultat->setTauxRemplissage($resultatDonnees['tauxRemplissage']);
            $resultat->setPdf($resultatDonnees['pdf']);
            $resultat->setStatut($this->_referenceManager->findOneBy(array('id' => intval($resultatDonnees['statut']) )));
            $resultat->setRemarque($resultatDonnees['remarque']);
            $resultat->setOutil($outil);
            if(!is_null($resultatDonnees['user']))
            {
                $user = $this->_userManager->findOneBy(array('id' => intval($resultatDonnees['user']) ));
                if(!is_null($user))
                {
                    $resultat->setUser( $user );
                }
            }
            $resultat->setSynthese($resultatDonnees['synthese']);

            if(array_key_exists(intval($resultatDonnees['id']), $arraySyntheses))
            {
                foreach ($arraySyntheses[intval($resultatDonnees['id'])] as $syntheseId)
                {
                    if(array_key_exists(intval($syntheseId), $arrayIdResultats))
                    {
                        $resultat->addResultat($this->findOneBy(array('id' => $arrayIdResultats[intval($syntheseId)] )));
                    }
                }
            }

            $this->save( $resultat );

            $arrayIdResultats[intval($resultatDonnees['id'])] = $resultat->getId();
        }

        return $arrayIdResultats;
    }
}
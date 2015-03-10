<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\ChapitreManager as ChapitreManagerAutodiag;
use Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Manager\ResultatManager;

/**
 * Manager de l'entité Categorie.
 */
class ImportExcelChapitreManager extends ChapitreManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Chapitre';


    /**
     * Constructeur du manager gérant les chapitres d'outil.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \HopitalNumerique\AutodiagBundle\Manager\ResultatManager $resultatManager Le manager de l'entité Resultat
     * @return void
     */
    public function __construct(EntityManager $entityManager, ResultatManager $resultatManager)
    {
        parent::__construct($entityManager, $resultatManager);
    }

    /**
     * Récupération des données des catégories du fichier excel d'import
     *
     * @param Array $arrayChapitres Tableau des données de catégories à persist
     *
     * @return void
     */
    public function saveChapitreImported( $arrayChapitres, $outil )
    {
        $arrayIdsChapitres = array();
        
        foreach ($arrayChapitres as $key => $chapitreDonnees) 
        {
            $chapitre = null;

            //Si il y a un id on vérifie qu'il correspond à un champ en base pour de l'édition, sinon l'ajoute
            if(!is_null($chapitreDonnees['id']))
            {
                $chapitre = $this->findOneBy(array('id' => $chapitreDonnees['id']));
            }
            if(is_null($chapitre))
            {
                //Création d'une nouvelle catégorie
                $chapitre = $this->createEmpty();
            }

            $tool  = new Chaine( $chapitreDonnees['libelle'] );
            $chapitre->setAlias( $tool->minifie() );

            $chapitre->setTitle($chapitreDonnees['libelle']);

            //$chapitre->setNote($chapitreDonnees['note']);
            $chapitre->setCode($chapitreDonnees['code']);
            $chapitre->setNoteOptimale($chapitreDonnees['noteOptimale']);
            $chapitre->setNoteMinimale($chapitreDonnees['noteMinimale']);
            $chapitre->setSynthese($chapitreDonnees['synthese']);
            $chapitre->setIntro($chapitreDonnees['introduction']);
            $chapitre->setDesc($chapitreDonnees['description']);
            $chapitre->setLien($chapitreDonnees['lien']);
            $chapitre->setDescriptionLien($chapitreDonnees['descriptionLien']);
            $chapitre->setAffichageRestitutionBarre($chapitreDonnees['affichageRestitutionBarre']);
            $chapitre->setAffichageRestitutionRadar($chapitreDonnees['affichageRestitutionRadar']);
            $chapitre->setOrder($key + 1);

            if (trim($chapitreDonnees['idParent']) !== '' && isset($arrayIdsChapitres[$chapitreDonnees['idParent']]))
            {
                $chapitreParent = $this->findOneBy(array('id' => $arrayIdsChapitres[$chapitreDonnees['idParent']], 'outil' => $outil));
                $chapitre->setParent($chapitreParent);
            }
            elseif (trim($chapitreDonnees['codeParent']) !== '' )
            {
                $chapitreParent = $this->findOneBy(array('code' => $chapitreDonnees['codeParent'], 'outil' => $outil));
                $chapitre->setParent($chapitreParent);
            }

            $chapitre->setOutil($outil);

            $this->save( $chapitre );
            
            $arrayIdsChapitres[('' != $chapitreDonnees['id'] ? $chapitreDonnees['id'] : $chapitre->getId())] = $chapitre->getId();
        }
        
        return $arrayIdsChapitres;
    }
}

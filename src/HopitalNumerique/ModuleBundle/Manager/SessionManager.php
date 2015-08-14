<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Session.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Session';
    
    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager ReponseManager
     */
    private $reponseManager;
    
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;
    
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $_userManager;
    
    /**
     * Constructeur du manager de Session.
     * 
     * @param \Doctrine\ORM\EntityManager $em EntityManager
     * @param \HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager $reponseManager ReponseManager
     * @param \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager ReferenceManager
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager UserManager
     */
    public function __construct(EntityManager $em, ReponseManager $reponseManager, ReferenceManager $referenceManager, UserManager $userManager)
    {
        parent::__construct($em);
        
        $this->reponseManager   = $reponseManager;
        $this->referenceManager = $referenceManager;
        $this->_userManager     = $userManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $sessions = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        $result = array();

        foreach ($sessions as $key => $session) 
        {
            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $session->getNombrePlaceDisponible();

            foreach ($session->getInscriptions() as $inscription) 
            {
                if($inscription->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscription->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $result[$key] = array(
                'id'                       => $session->getId(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession'              => $session->getDateSession(),
                'duree'                    => $session->getDuree()->getLibelle(),
                'horaires'                 => $session->getHoraires(),
                'nbInscrits'               => $nbInscritsAccepte,
                'nbInscritsEnAttente'      => $nbInscritsEnAttente,
                'placeRestantes'           => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat'                     => $session->getEtat()->getLibelle(),
                'archiver'                 => $session->getArchiver()
            );
        }

        return $result;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid( $condition = null )
    {
        $domainesIds     = $this->_userManager->getUserConnected()->getDomainesId();

        $sessions = $this->getRepository()->getAllDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($sessions as $key => $session) 
        {
            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $session->getNombrePlaceDisponible();

            foreach ($session->getInscriptions() as $inscription) 
            {
                if($inscription->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscription->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $domaineNom = '';
            foreach ($session->getModule()->getDomaines() as $domaine) 
            {
                if($domaineNom !== '')
                {
                    $domaineNom .= ' ; ';
                }
                $domaineNom .= $domaine->getNom();
            }

            $result[$key] = array(
                'id'                       => $session->getId(),
                'moduleTitre'              => $session->getModule()->getTitre(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession'              => $session->getDateSession(),
                'duree'                    => $session->getDuree()->getLibelle(),
                'horaires'                 => $session->getHoraires(),
                'nbInscrits'               => $nbInscritsAccepte,
                'nbInscritsEnAttente'      => $nbInscritsEnAttente,
                'placeRestantes'           => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat'                     => $session->getEtat()->getLibelle(),
                'archiver'                 => $session->getArchiver(),
                'domaineNom'               => $domaineNom
            );
        }

        return $result;
    }

    /**
     * Retourne la liste des sessions du domaine courrant étant en court d'inscription et active
     *
     * @param idDomaine $idDomaine Domaine concerné
     * 
     * @return array
     */
    public function getSessionsInscriptionOuverteModuleDomaine( $idDomaine )
    {
        return $this->getRepository()->getSessionsInscriptionOuverteModuleDomaine( $idDomaine )->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions du formateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getSessionsForFormateur( $user )
    {
        return $this->getRepository()->getSessionsForFormateur( $user )->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions à évaluer pour le dashboard user
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getSessionsForDashboard( $user )
    {
        return $this->getRepository()->getSessionsForDashboard( $user )->getQuery()->getResult();
    }

    /**
     * Retourne les sessions des 15 prochains jours
     * 
     * @return array
     */
    public function getNextSessions()
    {
        return $this->getRepository()->getNextSessions()->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions ou l'user connecté est formateur
     *
     * @param User $user L'utilisateur connecté
     *
     * @return array
     */
    public function getSessionsForFormateurForDashboard( $user )
    {
        $before = $this->getRepository()->getSessionsForFormateur( $user, 'beforeToday', 2 )->getQuery()->getResult();
        $after  = $this->getRepository()->getSessionsForFormateur( $user, 'afterToday', 2 )->getQuery()->getResult();

        return array('before' => $before, 'after' => $after);
    }

    /**
     * Retourne les évaluations pour l'export.
     *
     * @param integer[] $sessionIds Les IDs des sessions à exporter
     * @param string $charset Encodage du CSV
     * @return array Données pour l'export
     */
    public function getExportEvaluationsCsv(array $sessionIds, $charset)
    {
        $sessions = $this->findBy(array('id' => $sessionIds));
        
        
        $colonnes = array
        (
            'Nom du module',
            'Date de session',
            'Participant - ID',
            'Participant - Nom'
        );
        $datas    = array();
        
        foreach ($sessions as $session)
        {
            $inscriptions = $session->getInscriptionsAccepte();
            foreach($inscriptions as $inscription)
            {
                $hasReponses = false;
                $user     = $inscription->getUser();
                $reponses = $this->reponseManager->reponsesByQuestionnaireByUser( 4, $user->getId() , true, $session->getId() );
                $row      = array();
            
                foreach($reponses as $reponse)
                {
                    $question   = $reponse->getQuestion();
                    $idQuestion = $question->getId();
            
                    //ajoute la question si non présente dans les colonnes
                    if( !isset($colonnes[$idQuestion]) )
                        $colonnes[$idQuestion] = $question->getLibelle();
            
                    //handle la réponse
                    switch($question->getTypeQuestion()->getLibelle())
                    {
                        case 'checkbox':
                            $row[$idQuestion] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non' );
                            break;
                        case 'entityradio':
                            $question = $reponse->getQuestion();
            
                            $referenceReponse = $this->referenceManager->findOneBy( array( 'id' => $reponse->getReponse()) );
            
                            if(!is_null($referenceReponse))
                            {
                                $row[$idQuestion] = $referenceReponse->getLibelle();
                            }
                            else
                            {
                                $row[$idQuestion] = 'Non renseigné';
                            }
                            break;
                        default:
                            $row[$idQuestion] = $reponse->getReponse();
                            break;
                    }
            
                    $hasReponses = true;
                }
            
                if(!$hasReponses)
                    continue;
            
                ksort($row);
            
                $tab = array_merge
                (
                    array
                    (
                        $session->getModule()->__toString(),
                        (null !== $session->getDateSession() ? $session->getDateSession()->format('d/m/y') : ''),
                        (null !== $inscription->getUser() ? $inscription->getUser()->getId() : ''),
                        (null !== $inscription->getUser() ? $inscription->getUser()->getAppellation() : '')
                    ),
                    $row
                );
                $datas[] = $tab;
            }
        }
        
        if(empty($datas))
        {
            $colonnes = array(0 => "Aucune donnée");
            $datas[] = array(0 => "");
        }
        
        ksort($colonnes);

        
    
        return $this->exportCsv
        (
            array_values($colonnes),
            $datas,
            'export-session-evaluations.csv',
            $charset
        );
    }
}

<?php
/**
 * Manager de Process.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;

/**
 * Manager de Process.
 */
class ProcessManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Process';

    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ChapitreManager Le manager de l'entité Chapitre
     */
    protected $chapitreManager;
    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ResultatManager Le manager de l'entité Resultat
     */
    private $resultatManager;
    

    /**
     * Constructeur du manager gérant les process d'outil.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \HopitalNumerique\AutodiagBundle\Manager\ChapitreManager $chapitreManager Le manager de l'entité Chapitre
     * @param \HopitalNumerique\AutodiagBundle\Manager\ResultatManager $resultatManager Le manager de l'entité Resultat
     * @return void
     */
    public function __construct(EntityManager $entityManager, ChapitreManager $chapitreManager, ResultatManager $resultatManager)
    {
        parent::__construct($entityManager);
        $this->chapitreManager = $chapitreManager;
        $this->resultatManager = $resultatManager;
    }
    
    
    /**
     * Retourne les données formatées de restitution par processus.
     * 
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil Outil
     * @return array Données formatées pour l'affichage de la restitution par processus
     */
    public function getDonneesRestitutionParProcessus(Resultat $resultat)
    {
        $donneesProcessSynthese = array();

        //Dans le cas d'une synthese, on récupère le min et max
        if($resultat->getSynthese())
        {
            foreach ($resultat->getResultats() as $resultatSynthese) 
            {
                $processes = $resultat->getOutil()->getProcess();
                $processesChapitresEnfants = $this->getChapitresEnfantsByProcesses($resultatSynthese->getOutil()->getProcess()->toArray());
                $this->chapitreManager->setResultatsMoyennes($resultatSynthese, $processesChapitresEnfants);
        
                // Processes
                foreach ($processes as $keyProcess => $process)
                {
                    //<-- Chapitres
                    foreach ($process->getChapitres() as $keyChapitre => $chapitre)
                    {
                        //<-- On récupère les chapitres enfants depuis l'ensemble des chapitres enfants des processus (pour n'avoir qu'une seule requête)
                        $chapitresEnfants = array();
                        foreach ($processesChapitresEnfants as $chapitreEnfant)
                            if ($chapitreEnfant->getParent()->getId() == $chapitre->getId())
                                $chapitresEnfants[] = $chapitreEnfant;
                        //-->
                    
                        // Chapitres enfants
                        foreach ($chapitresEnfants as $chapitreEnfant)
                        {
                            if(array_key_exists($chapitreEnfant->getId(), $donneesProcessSynthese))
                            {
                                if($donneesProcessSynthese[$chapitreEnfant->getId()]['min'] > ceil($chapitreEnfant->getResultatsMoyenne()))
                                {
                                    $donneesProcessSynthese[$chapitreEnfant->getId()]['min'] = ceil($chapitreEnfant->getResultatsMoyenne());
                                }
                                if($donneesProcessSynthese[$chapitreEnfant->getId()]['max'] < ceil($chapitreEnfant->getResultatsMoyenne()))
                                {
                                    $donneesProcessSynthese[$chapitreEnfant->getId()]['max'] = ceil($chapitreEnfant->getResultatsMoyenne());
                                }
                            }
                            else
                            {
                                $donneesProcessSynthese[$chapitreEnfant->getId()] = array
                                (
                                    'min' => ceil($chapitreEnfant->getResultatsMoyenne()),
                                    'max' => ceil($chapitreEnfant->getResultatsMoyenne())
                                );
                            }
                        }
                    }
                }
            }
        }

        $donneesProcess = array();
        $processesChapitresEnfants = $this->getChapitresEnfantsByProcesses($resultat->getOutil()->getProcess()->toArray());
        $this->chapitreManager->setResultatsMoyennes($resultat, $processesChapitresEnfants);

        $processes = $resultat->getOutil()->getProcess();
    
        // Processes
        foreach ($processes as $processId => $process)
        {
            $donneesProcessChapitres = array();
            
            //<-- Chapitres
            foreach ($process->getChapitres() as $chapitre)
            {
                //<-- On récupère les chapitres enfants depuis l'ensemble des chapitres enfants des processus (pour n'avoir qu'une seule requête)
                $chapitresEnfants = array();
                foreach ($processesChapitresEnfants as $chapitreEnfant)
                    if ($chapitreEnfant->getParent()->getId() == $chapitre->getId())
                        $chapitresEnfants[] = $chapitreEnfant;
                //-->
                
                $donneesProcessChapitresEnfants = array();
            
                // Chapitres enfants
                foreach ($chapitresEnfants as $chapitreEnfant)
                {
                    //donneesProcessSynthese
                    
                    $chapitreEnfantArray = array
                    (
                        'titre' => $chapitreEnfant->getTitle(),
                        'moyenne' => ceil($chapitreEnfant->getResultatsMoyenne()),
                        'nombreQuestionsRepondues' => $chapitreEnfant->getNombreQuestionsRepondues(),
                        'min' => null,
                        'max' => null
                    );

                    if(array_key_exists($chapitreEnfant->getId(), $donneesProcessSynthese))
                    {
                        if(array_key_exists('min', $donneesProcessSynthese[$chapitreEnfant->getId()]))
                        {
                            $chapitreEnfantArray['min'] = $donneesProcessSynthese[$chapitreEnfant->getId()]['min'];
                        }
                        if(array_key_exists('max', $donneesProcessSynthese[$chapitreEnfant->getId()]))
                        {
                            $chapitreEnfantArray['max'] = $donneesProcessSynthese[$chapitreEnfant->getId()]['max'];
                        }
                    }

                    $donneesProcessChapitresEnfants[] = $chapitreEnfantArray;
                }
                //-->
            
                $donneesProcessChapitres[] = array(
                    'titre' => $chapitre->getTitle(),
                    'enfants' => $donneesProcessChapitresEnfants
                );
            }
            //-->
            
            $donneesProcess[] = array
            (
                'libelle' => $process->getLibelle(),
                'chapitres' => $donneesProcessChapitres
            );
        }
        //-->

        return $donneesProcess;
    }
    
    /**
     * Retourne tous les chapitres enfants d'un ensemble de process.
     * 
     * @param \HopitalNumerique\AutodiagBundle\Entity\Process[] $processes Processes dont il faut récupérer les chapitres enfants
     * @return \HopitalNumerique\AutodiagBundle\Entity\Chapitre[] Chapitres enfants des process
     */
    private function getChapitresEnfantsByProcesses(array $processes)
    {
        $chapitres = array();

        foreach ($processes as $process)
            foreach ($process->getChapitres() as $chapitre)
                $chapitres[] = $chapitre;

        return $this->chapitreManager->findBy(array('parent' => $chapitres));
    }
}

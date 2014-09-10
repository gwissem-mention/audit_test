<?php
/**
 * Contrôleur des processus pour les outils.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\AutodiagBundle\Entity\Process;

/**
 * Contrôleur des processus pour les outils.
 */
class ProcessController extends Controller
{
    /**
     * Ajoute un processus.
     * 
     * @param integer $numero Le numéro de processus attribué par SF2 lors de l'ajout.
     */
    public function addAction($numero)
    {
        return $this->render('HopitalNumeriqueAutodiagBundle:Process:add.html.twig', array(
            'numeroProcessus' => $numero
        ));
    }
    
    /**
     * Informations en JSON d'un process.
     * 
     * @param \HopitalNumerique\AutodiagBundle\Entity\Process $process Process dont il faut récupérer les informations
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant les informations du processus
     */
    public function jsonAction(Process $process)
    {
        $processInformations = array(
            'id' => $process->getId(),
            'libelle' => $process->getLibelle(),
            'chapitreIds' => array()
        );
        foreach ($process->getChapitres() as $chapitre)
            $processInformations['chapitreIds'][] = $chapitre->getId();
        
        return new \Symfony\Component\HttpFoundation\Response(json_encode($processInformations));
    }
}

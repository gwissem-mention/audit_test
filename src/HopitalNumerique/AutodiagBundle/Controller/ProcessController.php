<?php
/**
 * Contrôleur des processus pour les outils.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}

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
     */
    public function addAction()
    {
        $process = $this->get('hopitalnumerique_autodiag.manager.process')->createEmpty();
        $processFormulaire = $this->createForm('hopitalnumerique_autodiag_process', $process);

        return $this->render('HopitalNumeriqueAutodiagBundle:Process:add.html.twig', array(
            'processFormulaire' => $processFormulaire->createView()
        ));
    }
}

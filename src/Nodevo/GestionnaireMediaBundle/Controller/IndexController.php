<?php
/**
 * Contrôleur pour le gestionnaire de média dans la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace Nodevo\GestionnaireMediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur pour le gestionnaire de média dans la console administrative.
 */
class IndexController extends Controller
{
    /**
     * Vue pour le gestionnaire de média dans la console administrative.
     * 
     * @return \Symfony\Component\HttpFoundation\Response Vue du gestionnaire de média
     */
    public function indexAction()
    {
        return $this->render('NodevoGestionnaireMediaBundle::index.html.twig');
    }
}

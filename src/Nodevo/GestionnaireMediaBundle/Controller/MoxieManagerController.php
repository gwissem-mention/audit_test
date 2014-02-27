<?php
/**
 * Contrôleur pour le gestionnaire de média MoxieManager.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace Nodevo\GestionnaireMediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour le gestionnaire de média MoxieManager.
 */
class MoxieManagerController extends Controller
{
    /**
     * Vue de l'explorateur (frame) du gestionnaire de média MoxieManager.
     * 
     * @return \Symfony\Component\HttpFoundation\Response Vue de l'explorateur MoxieManager
     */
    public function frameAction()
    {
        return $this->render('NodevoGestionnaireMediaBundle:MoxieManager:frame.html.twig');
    }

    /**
     * Fichier API (api.php) utilisé par MoxieManager modifié pour être compatible avec un bundle.
     * 
     * @return \Symfony\Component\HttpFoundation\Response Réponse gérée par le fichier api.php de MoxieManager
     */
    public function apiAction()
    {
        $this->get('nodevo_gestionnaire_media.manager.moxie_manager')->appelleApi();

        return new Response();
    }

    /**
     * JSON comprenant les paramètres utilisés par MoxieManager dans ce bundle.
     *
     * @return \Symfony\Component\HttpFoundation\Response Objet JSON avec les paramètres de MoxieManager
     */
    public function jsonParametresAction()
    {
        $moxieManagerParametres = array(
            'baseUrl' => $this->container->get('templating.helper.assets')->getUrl('bundles/nodevogestionnairemedia/js/moxiemanager'),
            'apiPhpUrl' => $this->get('router')->generate('nodevo_gestionnaire_media_moxiemanager_api', array(), true)
        );

        return new Response(json_encode($moxieManagerParametres));
    }
}

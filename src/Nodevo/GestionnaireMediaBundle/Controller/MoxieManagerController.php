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
     * Vue de l'explorateur du gestionnaire de média MoxieManager.
     * 
     * @return \Symfony\Component\HttpFoundation\Response Vue de l'explorateur MoxieManager
     */
    public function explorateurAction()
    {
        return $this->render('NodevoGestionnaireMediaBundle:MoxieManager:explorateur.html.twig');
    }

    /**
     * Fichier API (api.php) utilisé par MoxieManager modifié pour être compatible avec un bundle.
     * @TODO Gestion des habilitations ici !
     * 
     * @return \Symfony\Component\HttpFoundation\Response Réponse gérée par le fichier api.php de MoxieManager
     */
    public function apiAction()
    {
        $moxieManagerDossiers = $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.media.dossiers');
        for ($i = 0; $i < count($moxieManagerDossiers); $i++)
            $moxieManagerDossiers[$i] = $this->get('request')->server->get('DOCUMENT_ROOT').$moxieManagerDossiers[$i];

        define('MOXIEMANAGER_FILESYSTEM_ROOTPATH', implode(';', $moxieManagerDossiers));
        define('MOXIEMANAGER_FILESYSTEM_EXTENSIONS', $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.extensions_autorisees'));
        define('MOXIEMANAGER_GENERAL_LANGUAGE', $this->get('nodevo_gestionnaire_media.service.moxie_manager')->getLangue());
        define('MOXIEMANAGER_JS_BASE_URL', $this->get('request')->server->get('DOCUMENT_ROOT').$this->container->get('templating.helper.assets')->getUrl('bundles/nodevogestionnairemedia/js/moxiemanager'));

        require_once(dirname(__FILE__).'/../DependencyInjection/moxiemanager/api.php');

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

<?php
namespace Nodevo\GestionnaireMediaBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manager de MoxieManager
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
class MoxieManagerManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;

    /**
     * Constructeur du manager de MoxieManager.
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Appelle l'API de MoxieManager qui initialise notamment la configuration par défaut.
     * 
     * @return void
     */
    public function appelleApi()
    {
        if ($this->container->get('nodevo_acl.manager.acl')->checkAuthorization($this->container->get('request')->attributes->get('_route'), $this->container->get('security.context')->getToken()->getUser()))
        {
            $moxieManagerDossiers = $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.media.dossiers');
            for ($i = 0; $i < count($moxieManagerDossiers); $i++)
                $moxieManagerDossiers[$i] = $this->container->get('request')->server->get('DOCUMENT_ROOT').$moxieManagerDossiers[$i];
        
            define('MOXIEMANAGER_FILESYSTEM_ROOTPATH', implode(';', $moxieManagerDossiers));
            define('MOXIEMANAGER_FILESYSTEM_EXTENSIONS', $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.extensions_autorisees'));
            define('MOXIEMANAGER_GENERAL_LANGUAGE', $this->container->get('nodevo_gestionnaire_media.service.moxie_manager')->getLangue());
            define('MOXIEMANAGER_JS_BASE_URL', $this->container->get('request')->server->get('DOCUMENT_ROOT').$this->container->get('templating.helper.assets')->getUrl('bundles/nodevogestionnairemedia/js/moxiemanager'));
    
            require_once(dirname(__FILE__).'/../DependencyInjection/moxiemanager/api.php');
        }
    }
}
<?php
namespace Nodevo\GestionnaireMediaBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\GestionnaireMediaBundle\DependencyInjection\MoxieManager;
use Nodevo\AclBundle\Manager\AclManager;

/**
 * Manager de MoxieManager
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
class MoxieManagerManager
{
    /**
     * @var \AppKernel Noyau de l'application
     */
    private $kernel;
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext SecurityContext de l'application
     */
    private $securityContext;
    /**
     * @var \Nodevo\GestionnaireMediaBundle\DependencyInjection\MoxieManager Service MoxieManager
     */
    private $moxieManager;
    /**
     * @var \Nodevo\AclBundle\Manager\AclManager Manager de Acl
     */
    private $aclManager;
    /**
     * @var string Liste des extensions autorisées sur MoxieManager
     */
    private $extensionsAutorisees;
    /**
     * @var string Liste des dossiers de média MoxieManager
     */
    private $dossiersMedia;

    /**
     * Constructeur du manager de MoxieManager.
     * 
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Nodevo\GestionnaireMediaBundle\DependencyInjection\MoxieManager $moxieManager Service MoxieManager
     * @param \Nodevo\AclBundle\Manager\AclManager $aclManager Manager de Acl
     * @param string $dossiersMedia Liste des dossiers de média MoxieManager
     * @param string $extensionsAutorisees Liste des extensions autorisées sur MoxieManager
     * @return void
     */
    public function __construct(\AppKernel $kernel, SecurityContext $securityContext, MoxieManager $moxieManager, AclManager $aclManager, $dossiersMedia, $extensionsAutorisees)
    {
        $this->kernel = $kernel;
        $this->securityContext = $securityContext;
        $this->moxieManager = $moxieManager;
        $this->aclManager = $aclManager;
        $this->dossiersMedia = $dossiersMedia;
        $this->extensionsAutorisees = $extensionsAutorisees;
    }
    
    /**
     * Appelle l'API de MoxieManager qui initialise notamment la configuration par défaut.
     * 
     * @param string $documentRoot DOCUMENT_ROOT
     * @param string $managerJsUrl Chemin vers le dossier JS de MoxieManager
     * @param string $route Route actuelle
     * @return void
     */
    public function appelleApi($documentRoot, $managerJsUrl, $route)
    {
        if ($this->aclManager->checkAuthorization($route, $this->securityContext->getToken()->getUser()))
        {
            $moxieManagerDossiers = array();
            foreach ($this->dossiersMedia as $dossierMedia)
                $moxieManagerDossiers[] = $documentRoot.$dossierMedia;
        
            define('MOXIEMANAGER_FILESYSTEM_ROOTPATH', implode(';', $moxieManagerDossiers));
            define('MOXIEMANAGER_FILESYSTEM_EXTENSIONS', $this->extensionsAutorisees);
            define('MOXIEMANAGER_GENERAL_LANGUAGE', $this->moxieManager->getLangue());
            define('MOXIEMANAGER_JS_BASE_URL', $managerJsUrl);

            require_once($this->kernel->locateResource('@NodevoGestionnaireMediaBundle/DependencyInjection/moxiemanager/api.php'));
        }
    }
}
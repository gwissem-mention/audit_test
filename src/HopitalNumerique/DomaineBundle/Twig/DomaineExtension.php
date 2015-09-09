<?php
namespace HopitalNumerique\DomaineBundle\Twig;

use Symfony\Component\HttpFoundation\Request;

class DomaineExtension extends \Twig_Extension
{
    private $_container;

    public function __construct($container)
    {
        $this->_container = $container;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'domaineCurrent'                 => $this->getDomaineCurrent(),
            'domaineCurrentId'               => $this->getDomaineCurrentId(),
            'templateCurrentId'              => $this->getTemplateCurrentId(),
            'aliasMenuTemplateCurrent'       => $this->getMenuNameCurrentTemplate(),
            'aliasMenuFooterTemplateCurrent' => $this->getMenuNameFooterCurrentTemplate()
        );
    }

    /**
     * Récupère le domaine courant
     *
     * @return domaine id
     */
    public function getDomaineCurrent()
    {
        if (!$this->_container->isScopeActive('request')) 
        {
            return null;
        }
        return $this->_container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($this->_container->get('request')->getSession()->get('domaineId'));
    }

    /**
     * Récupère l'id du domaine courant
     *
     * @return domaine id
     */
    public function getDomaineCurrentId()
    {
        if (!$this->_container->isScopeActive('request')) 
        {
            return null;
        }
        return $this->_container->get('request')->getSession()->get('domaineId');
    }

    /**
     * Récupère l'id du template du domaine courant
     *
     * @return domaine id
     */
    public function getTemplateCurrentId()
    {
        if (!$this->_container->isScopeActive('request')) 
        {
            return null;
        }

        $template = $this->_container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($this->_container->get('request')->getSession()->get('domaineId'))->getTemplate();
        
        return $template->getId();
    }

    /**
     * Récupère le nom du menu à utiliser pour le template courrant
     *
     * @return string Alias du menu
     */
    public function getMenuNameCurrentTemplate()
    {
        if (!$this->_container->isScopeActive('request')) 
        {
            return null;
        }

        $idDomaine = $this->_container->get('request')->getSession()->get('domaineId');
        
        $aliasMenu = "menu-main-front_" . $idDomaine;
        $menu      = $this->_container->get('nodevo_menu.manager.menu')->findOneBy(array('alias' => $aliasMenu));

        return is_null($menu) ? 'menu-main-front_gen' : $menu->getAlias();
    }

    /**
     * Récupère le nom du menu du footer à utiliser pour le template courrant
     *
     * @return string Alias du menu
     */
    public function getMenuNameFooterCurrentTemplate()
    {
        if (!$this->_container->isScopeActive('request')) 
        {
            return null;
        }

        $idDomaine = $this->_container->get('request')->getSession()->get('domaineId');
        
        $aliasMenu = "menu-footer-front_" . $idDomaine;
        $menu      = $this->_container->get('nodevo_menu.manager.menu')->findOneBy(array('alias' => $aliasMenu));

        return is_null($menu) ? 'menu-footer-front_gen' : $menu->getAlias();
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'domaine_extension';
    }
}

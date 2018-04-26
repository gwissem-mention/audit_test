<?php

namespace HopitalNumerique\DomaineBundle\Twig;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Service\BaseUrlProvider;

class DomaineExtension extends \Twig_Extension
{
    private $_container;

    /**
     * @var CurrentDomaine
     */
    protected $currentDomaine;

    /**
     * DomaineExtension constructor.
     *
     * @param $container
     * @param CurrentDomaine $currentDomaine
     */
    public function __construct($container, CurrentDomaine $currentDomaine)
    {
        $this->_container = $container;
        $this->currentDomaine = $currentDomaine;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getBaseUrl', [$this, 'getBaseUrl']),
        ];
    }

    /**
     * Retourne la liste des filtres custom pour cette extension.
     *
     * @return array
     */
    public function getGlobals()
    {
        return [
            'domaineCurrent' => $this->getDomaineCurrent(),
            'domaineCurrentId' => $this->getDomaineCurrentId(),
            'templateCurrentId' => $this->getTemplateCurrentId(),
            'aliasMenuTemplateCurrent' => $this->getMenuNameCurrentTemplate(),
            'aliasMenuFooterTemplateCurrent' => $this->getMenuNameFooterCurrentTemplate(),
        ];
    }

    /**
     * Récupère le domaine courant.
     *
     * @return domaine id
     */
    public function getDomaineCurrent()
    {
        if (!$this->_container->isScopeActive('request')) {
            return null;
        }

        return $this->_container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($this->_container->get('request')->getSession()->get('domaineId'));
    }

    /**
     * Récupère l'id du domaine courant.
     *
     * @return domaine id
     */
    public function getDomaineCurrentId()
    {
        if (!$this->_container->isScopeActive('request')) {
            return null;
        }

        return $this->_container->get('request')->getSession()->get('domaineId');
    }

    /**
     * Récupère l'id du template du domaine courant.
     *
     * @return domaine id
     */
    public function getTemplateCurrentId()
    {
        if (!$this->_container->isScopeActive('request')) {
            return null;
        }

        $template = $this->_container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($this->_container->get('request')->getSession()->get('domaineId'))->getTemplate();

        return $template->getId();
    }

    /**
     * Récupère le nom du menu à utiliser pour le template courrant.
     *
     * @return string Alias du menu
     */
    public function getMenuNameCurrentTemplate()
    {
        if (!$this->_container->isScopeActive('request')) {
            return null;
        }

        $idDomaine = $this->_container->get('request')->getSession()->get('domaineId');

        $aliasMenu = 'menu-main-front_' . $idDomaine;
        $menu = $this->_container->get('nodevo_menu.manager.menu')->findOneBy(['alias' => $aliasMenu]);

        return is_null($menu) ? 'menu-main-front_gen' : $menu->getAlias();
    }

    /**
     * Récupère le nom du menu du footer à utiliser pour le template courrant.
     *
     * @return string Alias du menu
     */
    public function getMenuNameFooterCurrentTemplate()
    {
        if (!$this->_container->isScopeActive('request')) {
            return null;
        }

        $idDomaine = $this->_container->get('request')->getSession()->get('domaineId');

        $aliasMenu = 'menu-footer-front_' . $idDomaine;
        $menu = $this->_container->get('nodevo_menu.manager.menu')->findOneBy(['alias' => $aliasMenu]);

        return is_null($menu) ? 'menu-footer-front_gen' : $menu->getAlias();
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'domaine_extension';
    }

    /**
     * @param null $domains
     *
     * @return string
     */
    public function getBaseUrl($domains = null)
    {
        $baseUrlProvider = $this->_container->get('hopitalnumerique_domaine.service.base_url_provider');

        return $baseUrlProvider->getBaseUrl($domains ? $domains : [$this->currentDomaine->get()]);
    }
}

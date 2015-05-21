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
            'domaineCurrentId'  => $this->getDomaineCurrentId(),
            'templateCurrentId' => $this->getTemplateCurrentId(),
        );
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

        $template = $this->_container->get('hopitalnumerique_domaine.manager.domaine')->findOneById($this->_container->get('request')->getSession()->get('domaineId'));
        
        return $template->getId();
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
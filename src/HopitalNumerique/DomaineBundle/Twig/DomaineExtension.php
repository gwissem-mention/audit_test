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
            'domaineCurrentId' => $this->getDomaineCurrentId()
        );
    }

    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs
     *
     * @param Reponses[] $reponses         Listes des réponses
     * @param int        $questionnaireId  Questionnaire à vérifier
     * @param int        $paramId          Clé étrangère à vérifier
     *
     * @return boolean
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
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'domaine_extension';
    }
}
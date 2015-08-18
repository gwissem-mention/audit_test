<?php
namespace Nodevo\TexteDynamiqueBundle\Twig;

use Symfony\Component\HttpFoundation\Request;

class CodeExtension extends \Twig_Extension
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
    public function getFilters()
    {
        return array(
            'nodevoTexteDynamique' => new \Twig_Filter_Method($this, 'nodevoTexteDynamique')
        );
    }

    /**
     * Récupère la liste des publications lié à cette question
     *
     * @param string  $code      Code du texte à charger
     * @param integer $idDomaine Domaine courant
     *
     * @return string
     */
    public function nodevoTexteDynamique( $code, $idDomaine = null )
    {
        $textCode = '';
        $codesByDomaines = $this->_container->get('nodevo_textedynamique.manager.code')->getCodesByDomaines( $code );

        //Récupère le texte du domaine passé en param si il y en a un et que le domaineId n'est pas null, sinon retourne le texte de HN ou vide il n'y en a pas
        $textCode = !is_null($idDomaine) && array_key_exists($idDomaine, $codesByDomaines) ? $codesByDomaines[$idDomaine] : (array_key_exists(1, $codesByDomaines) ? $codesByDomaines[1] : '');
        
        return $textCode;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'textedynamique_extension';
    }
}
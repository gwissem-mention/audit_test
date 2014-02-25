<?php
namespace HopitalNumerique\ReferenceBundle\Twig;

class ReferenceExtension extends \Twig_Extension
{
    private $_managerRef;

    /**
     * Construit l'extension Twig en lui passant les 2 managers requis pour la checkAuthorization
     *
     * @param RefManager $managerRef Le manager des références
     */
    public function __construct($managerRef)
    {
        $this->_managerRef = $managerRef;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'reorder' => new \Twig_Filter_Method($this, 'reorder')
        );
    }

  
    /**
     * Vérifie que l'user à bien l'accès à la route
     *
     * @param array $options Tableau d'options
     *
     * @return boolean
     */
    public function reorder($options)
    {
        return $this->_managerRef->reorder( $options );
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'ref_extension';
    }
}
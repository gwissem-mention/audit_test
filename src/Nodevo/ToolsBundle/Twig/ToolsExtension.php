<?php
namespace Nodevo\ToolsBundle\Twig;

use Nodevo\ToolsBundle\Tools\Chaine;

class ToolsExtension extends \Twig_Extension
{
    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'minifieMoi' => new \Twig_Filter_Method($this, 'minifie')
        );
    }

    /**
     * Retourne une chaine de caractère minifiée
     *
     * @param string  $string               Chaine de caractère à minifier
     * @param string  $caractereSeparateur
     * @param boolean $toutEnMinuscule
     *
     * @return string
     */
    public function minifie( $string, $caractereSeparateur = '-', $toutEnMinuscule = true )
    {
        $tool = new Chaine( $string );
        $nomMinifier = $tool->minifie($caractereSeparateur, $toutEnMinuscule);

        return $nomMinifier;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'tools_extension';
    }
}
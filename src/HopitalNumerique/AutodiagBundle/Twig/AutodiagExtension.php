<?php

namespace HopitalNumerique\AutodiagBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AutodiagExtension extends \Twig_Extension
{
    private $container;

    /**
     * Construit l'extension Twig
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'checkNC' => new \Twig_Filter_Method($this, 'checkNC')
        );
    }

    public function checkNC( $value )
    {
        return ($value !== 'NC' && '' !== $value);
    }


    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'autodiag_extension';
    }
}

<?php

namespace HopitalNumerique\UserBundle\Util;

class OptionsConfig
{
    //Tableau d'options de config.yml
    protected $_options;

    public function __construct($options = array())
    {
        $this->_options = $options;
    }
    
    /**
     * Retourne le tableau d'options créé en config.yml
     * 
     * @return array Tableau des options créé en config.yml
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Retourne le tableau d'options créé en config.yml
     * 
     * @param string $label Clé de la valeur à récupérer
     *
     * @return string Valeur correspondant à la clé passé en param ou vide si la clé n'existe pas
     */
    public function getOptionsByLabel( $label )
    {        
        return array_key_exists($label, $this->_options) ?  $this->_options[$label] : '';
    }
}
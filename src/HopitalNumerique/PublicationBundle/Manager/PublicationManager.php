<?php

namespace HopitalNumerique\PublicationBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Report.
 */
class PublicationManager
{
    /**
     * Activation du cache
     * @var boolean Activation du cache
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    protected $_miseEnCache;
    
    /**
     * Constructeur du manager un booléen si on peut mettre en cache les publications
     *
     * @param Array         $options        Tableau d'options
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function __construct($options = array())
    {   
        $this->_miseEnCache = isset($options['miseEnCache']) ? $options['miseEnCache'] : array();
    }
    
    /**
     * Renvoie le boolean de mise en cache dans le config.yml
     * 
     * @return bool
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getMiseEnCache()
    {
        return $this->_miseEnCache;
    }
}
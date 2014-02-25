<?php
namespace Nodevo\AclBundle\Twig;

class AclExtension extends \Twig_Extension
{
    private $_managerAcl;

    /**
     * Construit l'extension Twig en lui passant le manager requis pour la checkAuthorization
     *
     * @param AclManager $managerAcl Le manager des acls
     */
    public function __construct($managerAcl)
    {
        $this->_managerAcl = $managerAcl;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'checkAuthorization' => new \Twig_Filter_Method($this, 'checkAuthorization')
        );
    }
  
    /**
     * Vérifie que l'user à bien l'accès à la route
     *
     * @param User   $user  Connected User
     * @param String $route La route concernée
     *
     * @return boolean
     */
    public function checkAuthorization($user, $url )
    {
        $result = $this->_managerAcl->checkAuthorization( $url, $user );

        return $result != -1 ? true : false;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'acl_extension';
    }
}
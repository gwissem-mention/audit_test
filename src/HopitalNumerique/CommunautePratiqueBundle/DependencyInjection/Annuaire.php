<?php
namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use HopitalNumerique\UserBundle\Manager\UserManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Classe gérant l'affichage des membres de la communauté de pratiques.
 */
class Annuaire
{
    /**
     * @var integer Nombre d'éléments à afficher par page
     */
    const NOMBRE_ELEMENTS_PAR_PAGE = 12;


    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager 
     */
    private $userManager;


    /**
     * Constructeur.
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }


    /**
     * Retourne les membres à afficher.
     * 
     * @param integer $page Numéro de page
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Membres
     */
    public function getPagerfantaUsers($page)
    {
        $adapter = new DoctrineORMAdapter($this->userManager->getCommunautePratiqueMembresQueryBuilder());
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(self::NOMBRE_ELEMENTS_PAR_PAGE);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }
}

<?php
namespace HopitalNumerique\CommunautePratiqueBundle\DependencyInjection;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

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
     * @var string Libellé du filtre Nomination
     */
    const FILTRE_NOMINATION_LABEL = 'nom';

    /**
     * @var string Libellé du filtre Profil d'ES
     */
    const FILTRE_ES_PROFIL_LABEL = 'profilEtablissementSante';

    /**
     * @var string Libellé du filtre Région
     */
    const FILTRE_REGION_LABEL = 'region';

    /**
     * @var string Libellé du filtre Type d'ES
     */
    const FILTRE_ES_TYPE_LABEL = 'statutEtablissementSante';

    /**
     * @var string Libellé du filtre Type d'activité
     */
    const FILTRE_ACTIVITE_TYPE_LABEL = 'typeActivite';


    /**
     * @var string Valeur des filtres
     */
    private static $FILTRES = array();


    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;


    /**
     * Constructeur.
     */
    public function __construct(SessionInterface $session, UserManager $userManager, ReferenceManager $referenceManager)
    {
        $this->session = $session;
        $this->userManager = $userManager;
        $this->referenceManager = $referenceManager;

        $this->initFiltres();
    }


    /**
     * Retourne les membres à afficher.
     * 
     * @param integer $page Numéro de page
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Membres
     */
    public function getPagerfantaUsers($page)
    {
        $usersQueryBuilder = $this->userManager->getCommunautePratiqueMembresQueryBuilder();
        
        $adapter = new DoctrineORMAdapter($this->applyFiltersInQueryBuilder($usersQueryBuilder));
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(self::NOMBRE_ELEMENTS_PAR_PAGE);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }

    /**
     * Retourne les membres d'un groupe à afficher.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     * @param integer                                                  $page   Numéro de page
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Membres
     */
    public function getPagerfantaUsersByGroupe(Groupe $groupe, $page)
    {
        $usersQueryBuilder = $this->userManager->getCommunautePratiqueMembresQueryBuilder($groupe);

        $adapter = new DoctrineORMAdapter($usersQueryBuilder);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(self::NOMBRE_ELEMENTS_PAR_PAGE);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }

    /**
     * Applique les filtres sur la requête de récupération des membres.
     * 
     * @param \HopitalNumerique\CommunautePratiqueBundle\DependencyInjection\QueryBuilder $query QueryBuilder
     */
    private function applyFiltersInQueryBuilder(QueryBuilder &$queryBuilder)
    {
        foreach ($this->getFiltres() as $filtreLibelle => $filtreValeur)
        {
            if (null !== $filtreValeur)
            {
                switch ($filtreLibelle)
                {
                    case self::FILTRE_NOMINATION_LABEL:
                        $queryBuilder
                            ->andWhere( $queryBuilder->expr()->orX( $queryBuilder->expr()->like('user.nom', ':'.$filtreLibelle), $queryBuilder->expr()->like('user.prenom', ':'.$filtreLibelle), $queryBuilder->expr()->like('user.email', ':'.$filtreLibelle) ) )
                            ->setParameter($filtreLibelle, '%'.$filtreValeur.'%')
                        ;
                        break;
                    case self::FILTRE_ACTIVITE_TYPE_LABEL:
                        $queryBuilder
                            ->andWhere( $queryBuilder->expr()->in( $filtreLibelle, ':'.$filtreLibelle ) )
                            ->setParameter($filtreLibelle, $filtreValeur)
                        ;
                        break;
                    default:
                        $queryBuilder
                            ->andWhere( $queryBuilder->expr()->in( 'user.'.$filtreLibelle, ':'.$filtreLibelle ) )
                            ->setParameter($filtreLibelle, $filtreValeur)
                        ;
                }
            }
        }

        return $queryBuilder;
    }


    /**
     * Initialise les filtres via la session.
     * 
     * @return void
     */
    private function initFiltres()
    {
        foreach ($this->getFiltreLibelles() as $filtreLibelle)
        {
            self::$FILTRES[$filtreLibelle] = $this->getFiltreSession($filtreLibelle);
        }
    }

    /**
     * Retourne la liste de tous les libellés de filtre.
     * 
     * @return array<string> Libellés
     */
    private function getFiltreLibelles()
    {
        return array(
            self::FILTRE_NOMINATION_LABEL,
            self::FILTRE_ES_PROFIL_LABEL,
            self::FILTRE_REGION_LABEL,
            self::FILTRE_ES_TYPE_LABEL,
            self::FILTRE_ACTIVITE_TYPE_LABEL
        );
    }

    /**
     * Retourne les valeurs de filtre.
     * 
     * @return array<string, mixed> Filtres
     */
    public function getFiltres()
    {
        $filtres = array();
        
        foreach (self::$FILTRES as $filtreLibelle => $filtreValeur)
        {
            switch ($filtreLibelle)
            {
                case self::FILTRE_ES_PROFIL_LABEL:
                case self::FILTRE_REGION_LABEL:
                case self::FILTRE_ES_TYPE_LABEL:
                case self::FILTRE_ACTIVITE_TYPE_LABEL:
                    $filtres[$filtreLibelle] = (null !== $filtreValeur && ( count($filtreValeur) > 0 ) ? $this->referenceManager->findBy(array('id' => $filtreValeur)) : null);
                    break;
                default:
                    $filtres[$filtreLibelle] = $filtreValeur;
            }
        }
        
        return $filtres;
    }
    
    /**
     * Retourne la valeur d'un filtre.
     * 
     * @param string $filtreLibelle Libellé du filtre
     * @return mixed Valeur
     */
    public function getFiltre($filtreLibelle)
    {
        switch ($filtreLibelle)
        {
            case self::FILTRE_ES_PROFIL_LABEL:
            case self::FILTRE_REGION_LABEL:
            case self::FILTRE_ES_TYPE_LABEL:
            case self::FILTRE_ACTIVITE_TYPE_LABEL:
                return (null !== self::$FILTRES[$filtreLibelle] && ( count(self::$FILTRES[$filtreLibelle]) > 0 ) ? $this->referenceManager->findBy(array('id' => self::$FILTRES[$filtreLibelle])) : null);
        }
        
        return self::$FILTRES[$filtreLibelle];
    }

    /**
     * Applique les filtres à l'annuaire et les conserve.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     */
    public function setFiltres(Request $request)
    {
        if ($request->request->has('hopitalnumerique_communautepratiquebundle_user_recherche'))
        {
            foreach ($this->getFiltreLibelles() as $filtreLibelle)
            {
                $this->setFiltre($filtreLibelle, $request->request->get('hopitalnumerique_communautepratiquebundle_user_recherche'));
            }
        }
    }

    /**
     * Initialise un filtre.
     * 
     * @param string $filtreLibelle Libellé du filtre
     * @param array  $requestPost   Request
     */
    private function setFiltre($filtreLibelle, array $requestPost)
    {
        self::$FILTRES[$filtreLibelle] = (isset($requestPost[$filtreLibelle]) && '' != $requestPost[$filtreLibelle] ? $requestPost[$filtreLibelle] : null);
        $this->saveFiltreSession($filtreLibelle);
    }


    /**
     * Retourne la valeur du filtre enregistré en session.
     * 
     * @param string $filtreLibelle Libellé du filtre
     * @return mixed Valeur du filtre
     */
    private function getFiltreSession($filtreLibelle)
    {
        return $this->session->get('cp-annuaire-'.$filtreLibelle, null);
    }

    /**
     * Enregistre en session le filtre.
     * 
     * @param string $filtreLibelle Libellé du filtre
     */
    private function saveFiltreSession($filtreLibelle)
    {
        $this->session->set('cp-annuaire-'.$filtreLibelle, self::$FILTRES[$filtreLibelle]);
    }
}

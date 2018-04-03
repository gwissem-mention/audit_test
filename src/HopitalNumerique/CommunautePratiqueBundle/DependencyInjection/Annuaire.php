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
 * Classe gérant l'affichage des membres de la communauté de pratique.
 */
class Annuaire
{
    /**
     * @var int Nombre d'éléments à afficher par page
     */
    const NOMBRE_ELEMENTS_PAR_PAGE = 12;

    /**
     * @var string Libellé du filtre Nomination
     */
    const FILTRE_NOMINATION_LABEL = 'nom';

    /**
     * @var string Libellé du filtre Profil d'ES
     */
    const FILTRE_ES_PROFIL_LABEL = 'profileType';

    /**
     * @var string Libellé du filtre Région
     */
    const FILTRE_REGION_LABEL = 'region';

    /**
     * @var string Libellé du filtre Type d'ES
     */
    const FILTRE_ES_TYPE_LABEL = 'organizationType';

    /**
     * @var string Libellé du filtre Type d'activité
     */
    const FILTRE_ACTIVITE_TYPE_LABEL = 'activities';

    /**
     * @var array Valeur des filtres
     */
    private static $FILTRES = [];

    /**
     * @var SessionInterface Session
     */
    private $session;

    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * Constructeur.
     *
     * @param SessionInterface $session
     * @param UserManager      $userManager
     * @param ReferenceManager $referenceManager
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
     * @param      $page
     * @param null $domaine
     * @param null $membreId
     *
     * @return Pagerfanta
     */
    public function getPagerfantaUsers($page, $domaine = null, $membreId = null)
    {
        $usersQueryBuilder = $this->userManager->getCommunautePratiqueMembresQueryBuilder(null, $domaine, $membreId);
        $adapter = new DoctrineORMAdapter($this->applyFiltersInQueryBuilder($usersQueryBuilder));
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(self::NOMBRE_ELEMENTS_PAR_PAGE);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }

    /**
     * Retourne les membres d'un groupe à afficher.
     *
     * @param Groupe $groupe
     * @param        $page
     *
     * @return Pagerfanta
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
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    private function applyFiltersInQueryBuilder(QueryBuilder &$queryBuilder)
    {
        foreach ($this->getFiltres() as $filtreLibelle => $filtreValeur) {
            if (null !== $filtreValeur) {
                switch ($filtreLibelle) {
                    case self::FILTRE_NOMINATION_LABEL:
                        $queryBuilder
                            ->leftJoin('user.computerSkills', 'computerSkills')
                            ->leftJoin('user.hobbies', 'hobbies')
                            ->andWhere(
                                $queryBuilder->expr()->orX(
                                    $queryBuilder->expr()->like('user.lastname', ':' . $filtreLibelle),
                                    $queryBuilder->expr()->like('user.firstname', ':' . $filtreLibelle),
                                    $queryBuilder->expr()->like('user.email', ':' . $filtreLibelle),
                                    $queryBuilder->expr()->like('user.presentation', ':' . $filtreLibelle),
                                    $queryBuilder->expr()->like('computerSkills.libelle', ':' . $filtreLibelle),
                                    $queryBuilder->expr()->like('hobbies.label', ':' . $filtreLibelle)
                                )
                            )
                            ->setParameter($filtreLibelle, '%' . $filtreValeur . '%')
                        ;
                        break;
                    case self::FILTRE_ACTIVITE_TYPE_LABEL:
                        $queryBuilder
                            ->andWhere($queryBuilder->expr()->in($filtreLibelle, ':' . $filtreLibelle))
                            ->setParameter($filtreLibelle, $filtreValeur)
                        ;
                        break;
                    default:
                        $queryBuilder
                            ->andWhere($queryBuilder->expr()->in('user.' . $filtreLibelle, ':' . $filtreLibelle))
                            ->setParameter($filtreLibelle, $filtreValeur)
                        ;
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * Initialise les filtres via la session.
     */
    private function initFiltres()
    {
        foreach ($this->getFiltreLibelles() as $filtreLibelle) {
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
        return [
            self::FILTRE_NOMINATION_LABEL,
            self::FILTRE_ES_PROFIL_LABEL,
            self::FILTRE_REGION_LABEL,
            self::FILTRE_ES_TYPE_LABEL,
            self::FILTRE_ACTIVITE_TYPE_LABEL,
        ];
    }

    /**
     * Retourne les valeurs de filtre.
     *
     * @return array<string, mixed> Filtres
     */
    public function getFiltres()
    {
        $filtres = [];

        foreach (self::$FILTRES as $filtreLibelle => $filtreValeur) {
            switch ($filtreLibelle) {
                case self::FILTRE_ES_PROFIL_LABEL:
                case self::FILTRE_REGION_LABEL:
                case self::FILTRE_ES_TYPE_LABEL:
                case self::FILTRE_ACTIVITE_TYPE_LABEL:
                    $filtres[$filtreLibelle] = (null !== $filtreValeur && (count($filtreValeur) > 0)
                        ? $this->referenceManager->findBy(['id' => $filtreValeur]) : null
                    );
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
     *
     * @return mixed Valeur
     */
    public function getFiltre($filtreLibelle)
    {
        switch ($filtreLibelle) {
            case self::FILTRE_ES_PROFIL_LABEL:
            case self::FILTRE_REGION_LABEL:
            case self::FILTRE_ES_TYPE_LABEL:
            case self::FILTRE_ACTIVITE_TYPE_LABEL:
                return null !== self::$FILTRES[$filtreLibelle] && (count(self::$FILTRES[$filtreLibelle]) > 0)
                    ? $this->referenceManager->findBy(['id' => self::$FILTRES[$filtreLibelle]]) : null
                ;
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
        if ($request->request->has('hopitalnumerique_communautepratiquebundle_user_recherche')) {
            foreach ($this->getFiltreLibelles() as $filtreLibelle) {
                $this->setFiltre(
                    $filtreLibelle,
                    $request->request->get('hopitalnumerique_communautepratiquebundle_user_recherche')
                );
            }
        }
    }

    /**
     * Supprime les filtres en session.
     */
    public function removeFiltres()
    {
        foreach ($this->getFiltreLibelles() as $filtreLibelle) {
            $this->session->remove('cp-annuaire-' . $filtreLibelle);
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
        self::$FILTRES[$filtreLibelle] = (isset($requestPost[$filtreLibelle]) && '' != $requestPost[$filtreLibelle]
            ? $requestPost[$filtreLibelle] : null
        );

        $this->saveFiltreSession($filtreLibelle);
    }

    /**
     * Retourne la valeur du filtre enregistré en session.
     *
     * @param string $filtreLibelle Libellé du filtre
     *
     * @return mixed Valeur du filtre
     */
    private function getFiltreSession($filtreLibelle)
    {
        return $this->session->get('cp-annuaire-' . $filtreLibelle, null);
    }

    /**
     * Enregistre en session le filtre.
     *
     * @param string $filtreLibelle Libellé du filtre
     */
    private function saveFiltreSession($filtreLibelle)
    {
        $this->session->set('cp-annuaire-' . $filtreLibelle, self::$FILTRES[$filtreLibelle]);
    }
}

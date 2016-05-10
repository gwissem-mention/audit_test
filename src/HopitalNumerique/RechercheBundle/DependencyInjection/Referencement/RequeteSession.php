<?php
namespace HopitalNumerique\RechercheBundle\DependencyInjection\Referencement;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\RechercheBundle\Manager\RequeteManager;
use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Enregistrement temporaire de la requête de recherche par référencement.
 */
class RequeteSession
{
    /**
     * @var string Label de la session des références
     */
    const SESSION_REFERENCES_NAME = 'hnrecherche_referencement_requete_references';

    /**
     * @var string Label de la session des catégories
     */
    const SESSION_CATEGORY_FILTERS_NAME = 'hnrecherche_referencement_requete_categories';

    /**
     * @var string Label de la session du texte
     */
    const SESSION_SEARCHED_TEXT_NAME = 'hnrecherche_referencement_requete_texte';

    /**
     * @var string Label de la session de la requete
     */
    const SESSION_REQUETE_NAME = 'hnrecherche_referencement_requete_requete';

    /**
     * @var string Label de la session de demande de sauvegarde
     */
    const SESSION_WANT_SAVE_REQUETE = 'hnrecherche_referencement_requete_wantsave';


    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\RechercheBundle\Manager\RequeteManager RequeteManager
     */
    private $requeteManager;


    /**
     * @var \HopitalNumerique\UserBundle\Entity\User|null User
     */
    private $user;

    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine Domaine courant
     */
    private $domaine;


    /**
     * Constructeur.
     */
    public function __construct(SessionInterface $session, ConnectedUser $connectedUser, CurrentDomaine $currentDomaine, RequeteManager $requeteManager)
    {
        $this->session = $session;
        $this->requeteManager = $requeteManager;

        $this->user = $connectedUser->get();
        $this->domaine = $currentDomaine->get();
    }


    /**
     * Retourne les IDs des références.
     *
     * @return array<integer> IDs des références
     */
    public function getReferenceIds()
    {
        return $this->session->get(self::SESSION_REFERENCES_NAME, []);
    }

    /**
     * Enregistre les références en session.
     *
     * @param array<integer> $referenceIds IDs des références
     */
    public function setReferenceIds(array $referenceIds)
    {
        $this->session->set(self::SESSION_REFERENCES_NAME, $referenceIds);
    }


    /**
     * Retourne les filtres de catégories.
     *
     * @return array Filtres
     */
    private function getCategoryFilters()
    {
        return $this->session->get(self::SESSION_CATEGORY_FILTERS_NAME, []);
    }

    /**
     * Enregistre les filtres de catégorie en session.
     *
     * @param array $categoryFilters Filtres
     */
    public function setCategoryFilters(array $categoryFilters)
    {
        $this->session->set(self::SESSION_CATEGORY_FILTERS_NAME, $categoryFilters);
    }

    /**
     * Retourne les IDs des types d'entité.
     *
     * @return array<integer>|null IDs
     */
    public function getEntityTypeIds()
    {
        $categoryFilters = $this->getCategoryFilters();

        if (array_key_exists(Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY, $categoryFilters)) {
            return $categoryFilters[Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY];
        }

        return null;
    }

    /**
     * Enregistre les types d'entité en session.
     *
     * @param array<integer> $entityTypeIds IDs des types d'entité
     */
    public function setEntityTypeIds(array $entityTypeIds = null)
    {
        $categoryFilters = $this->getCategoryFilters();

        if (null === $entityTypeIds) {
            if (array_key_exists(Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY, $categoryFilters)) {
                unset($categoryFilters[Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY]);
            }
        } else {
            $categoryFilters[Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY] = $entityTypeIds;
        }

        $this->setCategoryFilters($categoryFilters);
    }

    /**
     * Retourne les IDs des catégories de publication.
     *
     * @return array<integer>|null IDs
     */
    public function getPublicationCategoryIds()
    {
        $categoryFilters = $this->getCategoryFilters();

        if (array_key_exists(Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY, $categoryFilters)) {
            return $categoryFilters[Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY];
        }

        return null;
    }

    /**
     * Enregistre les catégories de publication en session.
     *
     * @param array<integer> $publicationCategoryIds IDs
     */
    public function setPublicationCategoryIds(array $publicationCategoryIds = null)
    {
        $categoryFilters = $this->getCategoryFilters();

        if (null === $publicationCategoryIds) {
            if (array_key_exists(Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY, $categoryFilters)) {
                unset($categoryFilters[Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY]);
            }
        } else {
            $categoryFilters[Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY] = $publicationCategoryIds;
        }

        $this->setCategoryFilters($categoryFilters);
    }


    /**
     * Retourne le texte.
     *
     * @return string Texte
     */
    public function getSearchedText()
    {
        return $this->session->get(self::SESSION_SEARCHED_TEXT_NAME, '');
    }

    /**
     * Enregistre le texte.
     *
     * @param string $searchedText Texte
     */
    public function setSearchedText($searchedText)
    {
        $this->session->set(self::SESSION_SEARCHED_TEXT_NAME, $searchedText);
    }


    /**
     * Retourne la requête.
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\Requete|null Requête
     */
    public function getRequete()
    {
        $requeteId = $this->session->get(self::SESSION_REQUETE_NAME);

        if (null !== $requeteId) {
            return $this->requeteManager->findOneById($requeteId);
        }

        return null;
    }

    /**
     * Enregistre la requête en session.
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\Requete $requete Requête
     */
    public function setRequete(Requete $requete)
    {
        $this->session->set(self::SESSION_REQUETE_NAME, $requete->getId());
        $this->setReferenceIds($requete->getRefs());
        $this->setCategoryFilters($requete->getCategPointDur());
        $this->setSearchedText($requete->getRechercheTextuelle());
    }

    /**
     * Supprime la session.
     */
    public function remove()
    {
        $this->session->remove(self::SESSION_REQUETE_NAME);
        $this->session->remove(self::SESSION_REFERENCES_NAME);
        $this->session->remove(self::SESSION_CATEGORY_FILTERS_NAME);
        $this->session->remove(self::SESSION_SEARCHED_TEXT_NAME);
    }

    /**
     * Spécifie si la requête demande à être sauvegardée (si utilisateur pas encore connecté).
     *
     * @param boolean $wantSave Sauvegarder ?
     */
    public function setWantToSaveRequete($wantSave)
    {
        $this->session->set(self::SESSION_WANT_SAVE_REQUETE, $wantSave);
    }

    /**
     * Retourne si la requête demande à être sauvegardée.
     *
     * @return boolean Sauvegarder ?
     */
    public function isWantToSaveRequete()
    {
        return $this->session->get(self::SESSION_WANT_SAVE_REQUETE, false);
    }

    /**
     * Enregistre la requête actuelle pour l'utilisateur connecté.
     */
    public function saveAsNewRequete(User $user = null)
    {
        $requeteUser = (null !== $user ? $user : $this->user);

        if (null !== $requeteUser) {
            $referenceIds = $this->getReferenceIds();

            if (count($referenceIds) > 0) {
                $requete = $this->requeteManager->createEmpty();
                $requete->setNom('Ma requête du '.date('d/m/Y à H:i'));
                $requete->setIsDefault(false);
                $requete->setIsUserNotified(false);
                $requete->setUser($requeteUser);
                $requete->setDomaine($this->domaine);
                $this->saveRequete($requete);
            }
        }
    }

    /**
     * Enregistre la requête.
     */
    public function saveRequete(Requete $requete)
    {
        $requete->setRefs($this->getReferenceIds());
        $requete->setCategPointDur($this->getCategoryFilters());
        $requete->setRechercheTextuelle($this->getSearchedText());
        $this->requeteManager->save($requete);
        $this->setRequete($requete);
    }
}

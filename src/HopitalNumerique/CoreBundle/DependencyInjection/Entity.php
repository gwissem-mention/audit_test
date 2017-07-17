<?php

namespace HopitalNumerique\CoreBundle\DependencyInjection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Manager\GroupeManager as CommunautePratiqueGroupeManager;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ForumBundle\Manager\TopicManager;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\PublicationBundle\Repository\SuggestionRepository;
use HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses;
use HopitalNumerique\RechercheBundle\Manager\ExpBesoinReponsesManager;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Manager\RechercheParcoursManager;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;
use Nodevo\TexteDynamiqueBundle\Manager\CodeManager as TexteDynamiqueCodeManager;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Nodevo\TexteDynamiqueBundle\Manager\CodeManager;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\Routing\RouterInterface;

/**
 * Service gérant une entité au sens générique (notamment pour le référencement).
 */
class Entity
{
    /**
     * @var int Type publication
     */
    const ENTITY_TYPE_OBJET = 1;

    /**
     * @var int Type infradoc
     */
    const ENTITY_TYPE_CONTENU = 2;

    /**
     * @var int Type Topic de forum
     */
    const ENTITY_TYPE_FORUM_TOPIC = 3;

    /**
     * @var int Type Ambassadeur
     */
    const ENTITY_TYPE_AMBASSADEUR = 4;

    /**
     * @var int Type RechercheParcours
     */
    const ENTITY_TYPE_RECHERCHE_PARCOURS = 5;

    /**
     * @var int Type Groupe de la communauté de pratique
     */
    const ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE = 6;

    /**
     * @var int Type Groupe de la communauté de pratique
     */
    const ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE = 7;

    /**
     * @var int Type Suggestion
     */
    const ENTITY_TYPE_SUGGESTION = 8;

    /**
     * @var int Forum board type
     */
    const ENTITY_TYPE_FORUM_BOARD = 9;

    /**
     * @var int Autodiag
     */
    const ENTITY_TYPE_AUTODIAG = 10;

    private $refForumTopicId;

    private $refAmbassadeurId;

    private $refRechercheParcoursId;

    private $refComPratiqueId;

    private $refExpressionBesoinReponseId;

    private $refForumBoardId;

    /**
     * @var RouterInterface Router
     */
    private $router;

    /**
     * @var CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    /**
     * @var UserManager UserManager
     */
    private $userManager;

    /**
     * @var ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var ContenuManager ContenuManager
     */
    private $contenuManager;

    /**
     * @var TopicManager TopicManager
     */
    private $forumTopicManager;

    /**
     * @var DomaineManager DomaineManager
     */
    private $domaineManager;

    /**
     * @var RechercheParcoursManager RechercheParcoursManager
     */
    private $rechercheParcoursManager;

    /**
     * @var CommunautePratiqueGroupeManager GroupeManager
     */
    private $communautePratiqueGroupeManager;

    /**
     * @var ExpBesoinReponsesManager ExpBesoinReponsesManager
     */
    private $expressionBesoinReponseManager;

    /**
     * @var CodeManager CodeManager
     */
    private $texteDynamiqueCodeManager;

    /**
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * @var SuggestionRepository
     */
    private $suggestionRepository;

    /**
     * @var EntityHasReferenceRepository $entityHasReferenceRepository
     */
    protected $entityHasReferenceRepository;

    /**
     * Constructeur.
     *
     * @param RouterInterface                 $router
     * @param CurrentDomaine                  $currentDomaine
     * @param UserManager                     $userManager
     * @param ObjetManager                    $objetManager
     * @param ContenuManager                  $contenuManager
     * @param TopicManager                    $forumTopicManager
     * @param DomaineManager                  $domaineManager
     * @param RechercheParcoursManager        $rechercheParcoursManager
     * @param CommunautePratiqueGroupeManager $communautePratiqueGroupeManager
     * @param ExpBesoinReponsesManager        $expressionBesoinReponseManager
     * @param CodeManager                     $texteDynamiqueCodeManager
     * @param ReferenceManager                $referenceManager
     * @param SuggestionRepository            $suggestionRepository
     * @param                                 $refForumTopicId
     * @param                                 $refAmbassadeurId
     * @param                                 $refRechercheParcoursId
     * @param                                 $refComPratiqueId
     * @param                                 $refExpressionBesoinReponseId
     * @param                                 $refForumBoardId
     * @param EntityHasReferenceRepository $entityHasReferenceRepository
     */
    public function __construct(
        RouterInterface $router,
        CurrentDomaine $currentDomaine,
        UserManager $userManager,
        ObjetManager $objetManager,
        ContenuManager $contenuManager,
        TopicManager $forumTopicManager,
        DomaineManager $domaineManager,
        RechercheParcoursManager $rechercheParcoursManager,
        CommunautePratiqueGroupeManager $communautePratiqueGroupeManager,
        ExpBesoinReponsesManager $expressionBesoinReponseManager,
        TexteDynamiqueCodeManager $texteDynamiqueCodeManager,
        ReferenceManager $referenceManager,
        SuggestionRepository $suggestionRepository,
        $refForumTopicId,
        $refAmbassadeurId,
        $refRechercheParcoursId,
        $refComPratiqueId,
        $refExpressionBesoinReponseId,
        $refForumBoardId,
        $entityHasReferenceRepository
    ) {
        $this->router = $router;
        $this->currentDomaine = $currentDomaine;
        $this->userManager = $userManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
        $this->forumTopicManager = $forumTopicManager;
        $this->domaineManager = $domaineManager;
        $this->rechercheParcoursManager = $rechercheParcoursManager;
        $this->communautePratiqueGroupeManager = $communautePratiqueGroupeManager;
        $this->expressionBesoinReponseManager = $expressionBesoinReponseManager;
        $this->texteDynamiqueCodeManager = $texteDynamiqueCodeManager;
        $this->referenceManager = $referenceManager;
        $this->suggestionRepository = $suggestionRepository;
        $this->refForumTopicId = $refForumTopicId;
        $this->refAmbassadeurId = $refAmbassadeurId;
        $this->refRechercheParcoursId = $refRechercheParcoursId;
        $this->refComPratiqueId = $refComPratiqueId;
        $this->refExpressionBesoinReponseId = $refExpressionBesoinReponseId;
        $this->refForumBoardId = $refForumBoardId;
        $this->entityHasReferenceRepository = $entityHasReferenceRepository;
    }

    /**
     * Retourne le type d'entité d'un objet.
     *
     * @param object $entity Entité
     *
     * @return int|null Type
     *
     * @throws \Exception
     */
    public function getEntityType($entity)
    {
        if (!is_object($entity)) {
            throw new \Exception('L\'entité n\'est pas un objet (type "' . gettype($entity) . '" trouvé).');
        }

        switch (true) {
            case $entity instanceof Objet:
                return self::ENTITY_TYPE_OBJET;
            case $entity instanceof Board:
                return self::ENTITY_TYPE_FORUM_BOARD;
            case $entity instanceof Contenu:
                return self::ENTITY_TYPE_CONTENU;
            case $entity instanceof Topic:
                return self::ENTITY_TYPE_FORUM_TOPIC;
            case $entity instanceof User:
                if ($entity->hasRoleAmbassadeur()) {
                    return self::ENTITY_TYPE_AMBASSADEUR;
                }
                break;
            case $entity instanceof RechercheParcours:
                return self::ENTITY_TYPE_RECHERCHE_PARCOURS;
            case $entity instanceof Groupe:
                return self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE;
            case $entity instanceof ExpBesoinReponses:
                return self::ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE;
            case $entity instanceof Suggestion:
                return self::ENTITY_TYPE_SUGGESTION;
            case $entity instanceof Autodiag:
                return self::ENTITY_TYPE_AUTODIAG;
        }

        return null;
    }

    /**
     * Retourne l'ID de l'objet.
     *
     * @param object $entity Entité
     *
     * @return int ID
     *
     * @throws \Exception
     */
    public function getEntityId($entity)
    {
        if (!is_object($entity)) {
            throw new \Exception('L\'entité n\'est pas un objet (type "' . gettype($entity) . '" trouvé).');
        }

        if (!method_exists($entity, 'getId')) {
            throw new \Exception('Méthode getId() non trouvé.');
        }

        return $entity->getId();
    }

    /**
     * Retourne l'entité selon son type et son ID.
     *
     * @param int $type Type
     * @param int $id   ID
     *
     * @return object|null Entité
     */
    public function getEntityByTypeAndId($type, $id)
    {
        $entities = $this->getEntitiesByTypeAndIds($type, [$id]);

        if (0 === count($entities)) {
            return null;
        }

        return $entities[0];
    }

    /**
     * Retourne l'entité selon son type et son ID.
     *
     * @param int   $type Type
     * @param array $ids  <integer> $ids  IDs des entités
     *
     * @return array <object> Entités
     *
     * @throws \Exception
     */
    public function getEntitiesByTypeAndIds($type, array $ids)
    {
        switch ($type) {
            case self::ENTITY_TYPE_OBJET:
                return $this->objetManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_CONTENU:
                return $this->contenuManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return $this->forumTopicManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_AMBASSADEUR:
                return $this->userManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->rechercheParcoursManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                return $this->communautePratiqueGroupeManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE:
                return $this->expressionBesoinReponseManager->findBy(['id' => $ids]);
            case self::ENTITY_TYPE_SUGGESTION:
                return $this->suggestionRepository->findBy(['id' => $ids]);
        }

        throw new \Exception('Type "' . $type . '" introuvable.');
    }

    //<-- Domaines

    /**
     * Retourne les dommaines d'une entité.
     *
     * @param object $entity Entité
     *
     * @return array <\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     *
     * @throws \Exception
     */
    public function getDomainesByEntity($entity)
    {
        if (method_exists($entity, 'getDomaines')) {
            // S'il s'agit d'un contenu sans domaine, on prend en compte les domaines de son objet
            if (0 === count($entity->getDomaines()) && $entity instanceof Contenu) {
                return $this->getDomainesByEntity($entity->getObjet());
            }

            return $entity->getDomaines();
        }
        if (method_exists($entity, 'getDomaine')) {
            if (null === $entity->getDomaine()) {
                return [];
            }

            return [$entity->getDomaine()];
        }

        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_FORUM_TOPIC:
                $hasDomain = null !== $entity->getBoard()
                    && null !== $entity->getBoard()->getCategory()
                    && null !== $entity->getBoard()->getCategory()->getForum()
                    && null !== $entity->getBoard()->getCategory()->getForum()->getDomain()
                ;
                return $hasDomain ? [$entity->getBoard()->getCategory()->getForum()->getDomain()] : [];
            case self::ENTITY_TYPE_FORUM_BOARD:
                return [$entity->getCategory()->getForum()->getDomain()];
            case self::ENTITY_TYPE_CONTENU:
                return $this->getDomainesByEntity($entity->getObjet());
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->getDomainesByEntity($entity->getRecherchesParcoursGestion());
            case self::ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE:
                return $this->getDomainesByEntity($entity->getQuestion()->getExpBesoinGestion());
            case self::ENTITY_TYPE_SUGGESTION:
                return $entity->getDomains();
        }

        throw new \Exception('Domaines non trouvés pour l\'entité.');
    }

    /**
     * Retourne si telle entité est liée à tel domaine.
     *
     * @param object                                         $entity  Entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     *
     * @return bool Si a domaine
     */
    public function entityHasDomaine($entity, Domaine $domaine)
    {
        foreach ($this->getDomainesByEntity($entity) as $entityDomaine) {
            if ($domaine->equals($entityDomaine)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne les dommaines communs à l'entité et l'utilisateur.
     *
     * @param object                                   $entity Entité
     * @param \HopitalNumerique\UserBundle\Entity\User $user   User
     *
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getEntityDomainesCommunsWithUser($entity, User $user = null)
    {
        if (null === $user) {
            return [];
        }

        return $this->getDomainesCommunsWithUser($this->getDomainesByEntity($entity), $user);
    }

    /**
     * Retourne les dommaines en commun avec l'utilisateur.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @param \HopitalNumerique\UserBundle\Entity\User              $user     User
     *
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getDomainesCommunsWithUser($domaines, User $user)
    {
        $domainesCommuns = [];

        if ($domaines != null) {
            foreach ($domaines as $entityDomaine) {
                if ($user->hasDomaine($entityDomaine)) {
                    $domainesCommuns[] = $entityDomaine;
                }
            }
        }

        return $domainesCommuns;
    }

    /**
     * Traite les domaines d'une entité lors de la soumission d'un formulaire notamment.
     * Un objet peut posséder des domaines non visibles par l'utilisateur, ceux-ci ne sont donc pas soumis par le formulaire mais ne doivent pas être supprimés.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $allInitialDomaines Tous les domaines de l'entité (mais si invisible à l'utilisateur)
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $userChosenDomaines Tous les domaines choisis par l'utilisateur
     * @param \HopitalNumerique\UserBundle\Entity\User              $user               Utilisateur
     *
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines de l'entité
     */
    public function processSubmitedDomaines($allInitialDomaines, $userChosenDomaines, User $user)
    {
        if ($allInitialDomaines instanceof PersistentCollection) {
            $domaines = new ArrayCollection($allInitialDomaines->toArray());
        } else {
            $domaines = clone $allInitialDomaines;
        }

        //<-- Ajout des nouveaux domaines (choisis par l'utilisateur)
        foreach ($userChosenDomaines as $domaineChoisi) {
            if (!$domaines->contains($domaineChoisi)) {
                $domaines->add($domaineChoisi);
            }
        }
        //-->

        //<-- Suppression des domaines (supprimés par l'utilisateur)
        foreach ($allInitialDomaines as $domaine) {
            if ($user->hasDomaine($domaine)) {
                $isDomaineChoisi = false;
                foreach ($userChosenDomaines as $domaineChoisi) {
                    if ($domaineChoisi->equals($domaine)) {
                        $isDomaineChoisi = true;
                        break;
                    }
                }
                if (!$isDomaineChoisi) {
                    $domaines->removeElement($domaine);
                }
            }
        }
        //-->

        return $domaines;
    }

    //-->

    /**
     * Retourne le libellé de l'entité.
     *
     * @param object   $entity                 Entité
     * @param int|null $truncateCaractersCount Nombre de caractères à afficher
     * @param bool     $parentTitle            If true, displays the parent title of the entity
     *
     * @return string Libellé
     */
    public function getTitleByEntity($entity, $truncateCaractersCount = null, $parentTitle = true)
    {
        $title = null;

        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_OBJET:
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                $title = $entity->getTitre();
                break;
            case self::ENTITY_TYPE_CONTENU:
                if ($parentTitle) {
                    $title = $entity->getObjet()->getTitre();
                } else {
                    $title = $entity->getTitre();
                }
                break;
            case self::ENTITY_TYPE_FORUM_TOPIC:
                $title = $entity->getTitle();
                break;
            case self::ENTITY_TYPE_AMBASSADEUR:
                $title = $entity->getPrenomNom();
                break;
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                $title = $entity->getReference()->getLibelle();
                break;
            case self::ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE:
                $title = $entity->getLibelle();
                break;
            case self::ENTITY_TYPE_SUGGESTION:
                $title = $entity->getTitle();
                break;
            case self::ENTITY_TYPE_FORUM_BOARD:
                $title = $entity->getName();
                break;
            case self::ENTITY_TYPE_AUTODIAG:
                $title = $entity->getTitle();
                break;
        }

        if (null !== $title && null !== $truncateCaractersCount && strlen($title) > $truncateCaractersCount) {
            $title = '<span title="' . $title . '">' . substr($title, 0, $truncateCaractersCount) . '...</span>';
        }

        return $title;
    }

    /**
     * Retourne le sous-titre de l'entité.
     *
     * @param object $entity Entité
     *
     * @return string Sous-titre
     */
    public function getSubtitleByEntity($entity)
    {
        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_CONTENU:
                return $this->contenuManager->getPrefix($entity) . ' ' . $entity->getTitre();
        }

        return null;
    }

    /**
     * Retourne les ID de catégorie de l'entité.
     *
     * @param object $entity Entité
     *
     * @return array<integer> IDs
     */
    public function getCategoryIdsByEntity($entity)
    {
        switch ($this->getEntityType($entity)) {
            // Si contenu sans aucun type, on prend les types de son objet
            case self::ENTITY_TYPE_CONTENU:
                if (0 === count($entity->getTypes())) {
                    return $this->getCategoryIdsByEntity($entity->getObjet());
                }
            // no break
            case self::ENTITY_TYPE_OBJET:
                return $entity->getTypeIds();
                break;
        }

        return [];
    }

    /**
     * Retourne la catégorie de l'entité.
     *
     * @param object $entity Entité
     *
     * @return string|null Catégorie
     */
    public function getCategoryByEntity($entity)
    {
        $categories = [];

        switch ($this->getEntityType($entity)) {
            // Si contenu sans aucun type, on prend les types de son objet
            case self::ENTITY_TYPE_CONTENU:
                if (0 === count($entity->getTypes())) {
                    return $this->getCategoryByEntity($entity->getObjet());
                }
            // no break
            case self::ENTITY_TYPE_OBJET:
                $categories = $entity->getTypeLabels();
                break;
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return $this->referenceManager->findOneById($this->refForumTopicId)->getLibelle();
            case self::ENTITY_TYPE_AMBASSADEUR:
                return $this->referenceManager->findOneById($this->refAmbassadeurId)->getLibelle();
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->referenceManager->findOneById($this->refRechercheParcoursId)->getLibelle();
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                return $this->referenceManager->findOneById($this->refComPratiqueId)->getLibelle();
            case self::ENTITY_TYPE_EXPRESSION_BESOIN_REPONSE:
                return $this->referenceManager->findOneById($this->refExpressionBesoinReponseId)->getLibelle();
            case self::ENTITY_TYPE_FORUM_BOARD:
                return $this->referenceManager->findOneById($this->refForumBoardId)->getLibelle();
        }

        return implode(' &diams; ', $categories);
    }

    /**
     * Retourne la description de l'entité.
     *
     * @param object   $entity                 Entité
     * @param int|null $truncateCaractersCount Nombre de caractères à afficher
     *
     * @return string|null Description
     */
    public function getDescriptionByEntity($entity, $truncateCaractersCount = 255)
    {
        $description = null;

        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_FORUM_TOPIC:
                $description = $entity->getFirstPost()->getBody();
                break;
            case self::ENTITY_TYPE_OBJET:
                $description = $entity->getResume();
                break;
            case self::ENTITY_TYPE_CONTENU:
                $description = $entity->getContenu();
                break;
            case self::ENTITY_TYPE_AMBASSADEUR:
                $texteDynamique = $this->texteDynamiqueCodeManager->findOneByCodeAndDomaine('Module_recherche_ambassadeur', $this->currentDomaine->get());
                if (null !== $texteDynamique) {
                    $description = $texteDynamique->getTexte();
                }
                break;
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                $description = $entity->getDescription();
                break;
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                $description = $entity->getDescriptionCourte();
                break;
            case self::ENTITY_TYPE_SUGGESTION:
                $description = $entity->getSummary();
                break;
            case self::ENTITY_TYPE_FORUM_BOARD:
                $description = $entity->getDescription();
                break;
        }

        if (null !== $description) {
            $description = trim(strip_tags(html_entity_decode($description)));

            if (null !== $truncateCaractersCount && strlen($description) > $truncateCaractersCount) {
                $description = substr($description, 0, $truncateCaractersCount);
                if (strrpos($description, ' ') > 0) {
                    $description = substr($description, 0, strrpos($description, ' ')) . '...';
                }
            }

            if ('' == $description) {
                return null;
            }
        }

        return $description;
    }

    /**
     * @param $entity
     * @param bool $onlyPrimary
     *
     * @return EntityHasReference[]
     */
    public function getReferencesByEntity($entity, $onlyPrimary = false)
    {
        $domains = $this->getDomainesByEntity($entity);

        $references = $this->entityHasReferenceRepository->findByEntityTypeAndEntityIdAndDomaines(
            $this->getEntityType($entity),
            $this->getEntityId($entity),
            $domains instanceof Collection ? $domains->toArray() : $domains
        );

        if ($onlyPrimary) {
            $references = array_filter($references, function (EntityHasReference $reference) {
                return $reference->isPrimary() === true;
            });
        }

        return $references;
    }

    //<-- URL

    /**
     * Retourne l'URL de la page de l'entité.
     *
     * @param object $entity Entité
     *
     * @return string|null URL
     */
    public function getFrontUrlByEntity($entity)
    {
        $entityId = $this->getEntityId($entity);

        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_OBJET:
                return $this->router->generate('hopital_numerique_publication_publication_objet', ['id' => $entityId, 'alias' => $entity->getAlias()]);
            case self::ENTITY_TYPE_CONTENU:
                return $this->router->generate('hopital_numerique_publication_publication_contenu', ['idc' => $entityId, 'aliasc' => $entity->getAlias(), 'id' => $this->getEntityId($entity->getObjet()), 'alias' => $entity->getObjet()->getAlias()]);
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return $this->router->generate('ccdn_forum_user_topic_show', ['topicId' => $entityId, 'forumName' => $entity->getBoard()->getCategory()->getForum()->getName()]);
            case self::ENTITY_TYPE_AMBASSADEUR:
                $parameters = json_encode([
                    $entity->getEmail() => $entity->getFirstname() . ' ' . $entity->getLastname(),
                ]);

                return 'javascript:Contact_Popup.display(' . $parameters . ', window.location.href);';
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->router->generate(
                    'hopital_numerique_guided_search_show',
                    [
                        'guidedSearchReference' => $entityId,
                        'guidedSearchReferenceAlias' => (new Chaine($entity->getReference()->getLibelle()))->minifie()
                    ]
                );
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                return $this->router->generate('hopitalnumerique_communautepratique_groupe_view', ['groupe' => $entityId]);
            case self::ENTITY_TYPE_SUGGESTION:
                return $this->router->generate('hopitalnumerique_suggestion_back_edit', ['id' => $entityId]);
            case self::ENTITY_TYPE_FORUM_BOARD:
                return $this->router->generate('ccdn_forum_user_board_show', ['boardId' => $entityId]);
        }

        return null;
    }

    //<-- URL

    /**
     * Retourne l'URL de la page de l'entité.
     *
     * @param object $entity Entité
     *
     * @return string|null URL
     */
    public function getSourceByEntity($entity)
    {
        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_OBJET:
                return $entity->getSource();
            case self::ENTITY_TYPE_CONTENU:
                return $entity->getObjet()->getSource();
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return null;
            case self::ENTITY_TYPE_AMBASSADEUR:
                return null;
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return null;
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                return null;
        }

        return null;
    }

    /**
     * Retourne l'URL de la page gérant le référencement de l'entité.
     *
     * @param object $entity Entité
     *
     * @return string|null URL
     */
    public function getMangementUrlByEntity($entity)
    {
        if ($entity instanceof Contenu) {
            return $this->router->generate('hopitalnumerique_objet_objet_edit', [
                'id' => $entity->getObjet()->getId(),
                'infra' => 1,
            ]);
        }
        if ($entity instanceof Objet) {
            return $this->router->generate('hopitalnumerique_objet_objet_edit', [
                'id' => $entity->getId(),
            ]);
        }

        return null;
    }

    //-->
}

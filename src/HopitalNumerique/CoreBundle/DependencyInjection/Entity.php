<?php
namespace HopitalNumerique\CoreBundle\DependencyInjection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\CommunautePratiqueBundle\Manager\GroupeManager as CommunautePratiqueGroupeManager;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ForumBundle\Manager\TopicManager;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\RechercheParcoursBundle\Manager\RechercheParcoursManager;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Component\Routing\RouterInterface;

/**
 * Service gérant une entité au sens générique (notamment pour le référencement).
 */
class Entity
{
    /**
     * @var string Type publication
     */
    const ENTITY_TYPE_OBJET = 'publication';

    /**
     * @var string Type infradoc
     */
    const ENTITY_TYPE_CONTENU = 'infradocs';

    /**
     * @var string Type Topic de forum
     */
    const ENTITY_TYPE_FORUM_TOPIC = 'forum';

    /**
     * @var string Type Ambassadeur
     */
    const ENTITY_TYPE_AMBASSADEUR = 'ambassadeur';

    /**
     * @var string Type RechercheParcours
     */
    const ENTITY_TYPE_RECHERCHE_PARCOURS = 'demarche';

    /**
     * @var string Type Groupe de la communauté de pratiques
     */
    const ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE = 'commpratique';


    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager UserManager
     */
    private $userManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ContenuManager ContenuManager
     */
    private $contenuManager;

    /**
     * @var \HopitalNumerique\ForumBundle\Manager\TopicManager TopicManager
     */
    private $forumTopicManager;

    /**
     * @var \HopitalNumerique\DomaineBundle\Manager\DomaineManager DomaineManager
     */
    private $domaineManager;

    /**
     * @var \HopitalNumerique\RechercheParcoursBundle\Manager\RechercheParcoursManager RechercheParcoursManager
     */
    private $rechercheParcoursManager;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Manager\GroupeManager GroupeManager
     */
    private $communautePratiqueGroupeManager;


    /**
     * Constructeur.
     */
    public function __construct(RouterInterface $router, UserManager $userManager, ObjetManager $objetManager, ContenuManager $contenuManager, TopicManager $forumTopicManager, DomaineManager $domaineManager, RechercheParcoursManager $rechercheParcoursManager, CommunautePratiqueGroupeManager $communautePratiqueGroupeManager)
    {
        $this->router = $router;
        $this->userManager = $userManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
        $this->forumTopicManager = $forumTopicManager;
        $this->domaineManager = $domaineManager;
        $this->rechercheParcoursManager = $rechercheParcoursManager;
        $this->communautePratiqueGroupeManager = $communautePratiqueGroupeManager;
    }


    /**
     * Retourne le type d'entité d'un objet.
     *
     * @param object $entity Entité
     * @return string|null Type
     */
    public function getEntityType($entity)
    {
        if (!is_object($entity)) {
            throw new \Exception('L\'entité n\'est pas un objet (type "'.gettype($entity).'" trouvé).');
        }

        switch (get_class($entity)) {
            case 'HopitalNumerique\ObjetBundle\Entity\Objet':
                return self::ENTITY_TYPE_OBJET;
            case 'HopitalNumerique\ObjetBundle\Entity\Contenu':
                return self::ENTITY_TYPE_CONTENU;
            case 'HopitalNumerique\ForumBundle\Entity\Topic':
                return self::ENTITY_TYPE_FORUM_TOPIC;
            case 'HopitalNumerique\UserBundle\Entity\User':
                if ($entity->hasRoleAmbassadeur()) {
                    return self::ENTITY_TYPE_AMBASSADEUR;
                }
                break;
            case 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours':
                return self::ENTITY_TYPE_RECHERCHE_PARCOURS;
            case 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe':
                return self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE;
        }

        return null;
    }

    /**
     * Retourne l'ID de l'objet.
     *
     * @param object $entity Entité
     * @return integer ID
     */
    public function getEntityId($entity)
    {
        if (!is_object($entity)) {
            throw new \Exception('L\'entité n\'est pas un objet (type "'.gettype($entity).'" trouvé).');
        }

        if (!method_exists($entity, 'getId')) {
            throw new \Exception('Méthode getId() non trouvé.');
        }

        return $entity->getId();
    }

    /**
     * Retourne l'entité selon son type et son ID.
     *
     * @param string  $type Type
     * @param integer $id   ID
     * @return object|null Entité
     */
    public function getEntityByTypeAndId($type, $id)
    {
        switch ($type) {
            case self::ENTITY_TYPE_OBJET:
                return $this->objetManager->findOneById($id);
            case self::ENTITY_TYPE_CONTENU:
                return $this->contenuManager->findOneById($id);
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return $this->forumTopicManager->findOneById($id);
            case self::ENTITY_TYPE_AMBASSADEUR:
                return $this->userManager->findOneById($id);
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->rechercheParcoursManager->findOneById($id);
            case self::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                return $this->communautePratiqueGroupeManager->findOneById($id);
        }

        return null;
    }

    //<-- Domaines
    /**
     * Retourne les dommaines d'une entité.
     *
     * @param object $entity Entité
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
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
                return [$this->domaineManager->findOneById(Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID)];
            case self::ENTITY_TYPE_CONTENU:
                return $this->getDomainesByEntity($entity->getObjet());
            case self::ENTITY_TYPE_RECHERCHE_PARCOURS:
                return $this->getDomainesByEntity($entity->getRecherchesParcoursGestion());
        }

        throw new \Exception('Domaines non trouvés pour l\'entité.');
    }

    /**
     * Retourne si telle entité est liée à tel domaine.
     *
     * @param object                                         $entity  Entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return boolean Si a domaine
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
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getEntityDomainesCommunsWithUser($entity, User $user)
    {
        return $this->getDomainesCommunsWithUser($this->getDomainesByEntity($entity), $user);
    }

    /**
     * Retourne les dommaines en commun avec l'utilisateur.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @param \HopitalNumerique\UserBundle\Entity\User              $user     User
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getDomainesCommunsWithUser($domaines, User $user)
    {
        $domainesCommuns = [];

        foreach ($domaines as $entityDomaine) {
            if ($user->hasDomaine($entityDomaine)) {
                $domainesCommuns[] = $entityDomaine;
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
     * Retourne l'URL de la page gérant le référencement de l'entité.
     *
     * @param object $entity Entité
     * @return string|null URL
     */
    public function getMangementUrlByEntity($entity)
    {
        if ($entity instanceof Contenu) {
            return $this->router->generate('hopitalnumerique_objet_objet_edit', [
                'id' => $entity->getObjet()->getId(),
                'infra' => 1
            ]);
        }
        if ($entity instanceof Objet) {
            return $this->router->generate('hopitalnumerique_objet_objet_edit', [
                'id' => $entity->getId()
            ]);
        }

        return null;
    }
}
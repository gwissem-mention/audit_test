<?php
namespace HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ForumBundle\Manager\TopicManager;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Service gérant une entité de référencement.
 */
class Entity
{
    /**
     * @var string Type publication
     */
    const ENTITY_TYPE_PUBLICATION = 'publication';

    /**
     * @var string Type infradoc
     */
    const ENTITY_TYPE_INFRADOC = 'infradocs';

    /**
     * @var string Type Topic de forum
     */
    const ENTITY_TYPE_FORUM_TOPIC = 'forum';

    /**
     * @var string Type Ambassadeur
     */
    const ENTITY_TYPE_AMBASSADEUR = 'ambassadeur';


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
     * Constructeur.
     */
    public function __construct(UserManager $userManager, ObjetManager $objetManager, ContenuManager $contenuManager, TopicManager $forumTopicManager, DomaineManager $domaineManager)
    {
        $this->userManager = $userManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
        $this->forumTopicManager = $forumTopicManager;
        $this->domaineManager = $domaineManager;
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
                return self::ENTITY_TYPE_PUBLICATION;
            case 'HopitalNumerique\ObjetBundle\Entity\Contenu':
                return self::ENTITY_TYPE_INFRADOC;
            case 'HopitalNumerique\ForumBundle\Entity\Topic':
                return self::ENTITY_TYPE_FORUM_TOPIC;
            case 'HopitalNumerique\UserBundle\Entity\User':
                if ($entity->hasRoleAmbassadeur()) {
                    return self::ENTITY_TYPE_AMBASSADEUR;
                }
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
            case self::ENTITY_TYPE_PUBLICATION:
                return $this->objetManager->findOneById($id);
            case self::ENTITY_TYPE_INFRADOC:
                return $this->contenuManager->findOneById($id);
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return $this->forumTopicManager->findOneById($id);
            case self::ENTITY_TYPE_AMBASSADEUR:
                return $this->userManager->findOneById($id);
        }

        return null;
    }

    /**
     * Retourne les dommaines d'une entité.
     *
     * @param object $entity Entité
     * @return array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    public function getDomainesByEntity($entity)
    {
        if (method_exists($entity, 'getDomaines')) {
            if (0 === count($entity->getDomaines()) && $entity instanceof Contenu) {
                return $this->getDomainesByEntity($entity->getObjet());
            }
            return $entity->getDomaines();
        }

        switch ($this->getEntityType($entity)) {
            case self::ENTITY_TYPE_FORUM_TOPIC:
                return [$this->domaineManager->findOneById(Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID)];
            case self::ENTITY_TYPE_INFRADOC:
                return $this->getDomainesByEntity($entity->getObjet());
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
    public function getDomainesCommunsWithUser($entity, User $user)
    {
        $domainesCommuns = [];
        $entityDomaines = $this->getDomainesByEntity($entity);

        foreach ($entityDomaines as $entityDomaine) {
            if ($user->hasDomaine($entityDomaine)) {
                $domainesCommuns[] = $entityDomaine;
            }
        }

        return $domainesCommuns;
    }
}

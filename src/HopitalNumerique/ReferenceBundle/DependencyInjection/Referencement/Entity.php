<?php
namespace HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;

use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

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
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ContenuManager ContenuManager
     */
    private $contenuManager;


    /**
     * Constructeur.
     */
    public function __construct(ObjetManager $objetManager, ContenuManager $contenuManager)
    {
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
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
        }

        return null;
    }
}

<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use Doctrine\DBAL\Driver\Connection;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Migration.
 *
 * URL : /admin/referencement/cron-save-notes/PBYDHWURJYILOLP24FKGMERO78HD7SUXVRT
 */
class Migration
{
    /**
     *
     * @var \Symfony\Bridge\Doctrine\RegistryInterface Doctrine
     */
    private $doctrine;

    /**
     * @var \Doctrine\DBAL\Driver\Connection DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;


    /**
     * @var array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Toutes les références
     */
    private static $REFERENCES_BY_ID = [];


    /**
     * Constructeur.
     */
    public function __construct(RegistryInterface $doctrine, Connection $databaseConnection, EntityHasReferenceManager $entityHasReferenceManager)
    {
        $this->doctrine = $doctrine;
        $this->databaseConnection = $databaseConnection;
        $this->entityHasReferenceManager = $entityHasReferenceManager;

        $this->init();
    }

    /**
     * Initialisation.
     */
    private function init()
    {
        foreach ($this->doctrine->getRepository('\HopitalNumerique\ReferenceBundle\Entity\Reference')->findAll() as $reference) {
            self::$REFERENCES_BY_ID[$reference->getId()] = $reference;
        }
    }


    /**
     * Migre toutes les liaisons avec les références.
     */
    public function migreAll()
    {
        set_time_limit(0);
        $this->migreReferencesForEntities(Entity::ENTITY_TYPE_OBJET, 'hn_objet_reference', 'obj_id', 'refo_primary');
        $this->migreReferencesForEntities(Entity::ENTITY_TYPE_CONTENU, 'hn_objet_contenu_reference', 'con_id', 'refc_primary');
        $this->migreReferencesForEntities(Entity::ENTITY_TYPE_FORUM_TOPIC, 'hn_forum_topic_reference', 'topic_id', 'reftop_primary');
    }

    /**
     * Migre les références des objets.
     */
    private function migreReferencesForEntities($entityType, $referencesEntitiesTable, $entityProperty, $primaryProperty)
    {
        $referencesEntities = [];

        $sql = "
            SELECT *
            FROM $referencesEntitiesTable
            ";
        $results = $this->databaseConnection->query($sql)->fetchAll();

        foreach ($results as $result) {
            $entityId = $result[$entityProperty];
            if (!array_key_exists($entityId, $referencesEntities)) {
                $referencesEntities[$entityId] = [];
            }

            $referenceId = $result['ref_id'];
            $primary = ('1' == $result[$primaryProperty]);
            $reference = self::$REFERENCES_BY_ID[$referenceId];
            $referencesEntities[$entityId][] = [
                'reference' => $reference,
                'primary' => $primary
            ];
        }

        $this->deleteReferencesParentsByEntityId($referencesEntities);
        $this->saveReferencesHaveEntities($entityType, $referencesEntities);
    }

    /**
     * On supprime les références ayant des enfants dans la liste des références de chaque entité.
     */
    private function deleteReferencesParentsByEntityId(&$referencesEntitiesByEntityId)
    {
        foreach ($referencesEntitiesByEntityId as $entityId => &$referencesEntities) {
            $this->deleteReferencesParents($referencesEntities);
        }
    }
    
    private function deleteReferencesParents(&$referencesEntities)
    {
        $referenceParentIds = $this->getReferencesParentIdsFromReferencesEntities($referencesEntities);

        foreach ($referencesEntities as $i => $referenceEntity) {
            if (in_array($referenceEntity['reference']->getId(), $referenceParentIds)) {
                unset($referencesEntities[$i]);
            }
        }
    }

    private function getReferencesParentIdsFromReferencesEntities($referencesEntities)
    {
        $referenceParentIds = [];
        $references = $this->getReferencesFromReferencesEntities($referencesEntities);

        foreach ($references as $reference) {
            if ($this->referenceHasEnfant($reference, $references)) {
                $referenceParentIds[] = $reference->getId();
            }
        }

        return $referenceParentIds;
    }

    private function getReferencesFromReferencesEntities($referencesEntities)
    {
        $references = [];

        foreach ($referencesEntities as $referenceEntity) {
            $references[] = $referenceEntity['reference'];
        }

        return $references;
    }

    /**
     * Vérifie si une référence a au moins un enfant parmi la liste des références.
     */
    private function referenceHasEnfant(Reference $reference, array $references)
    {
        $hasEnfant = false;

        foreach ($reference->getEnfants() as $referenceEnfant) {
            foreach ($references as $reference) {
                if ($referenceEnfant->getId() == $reference->getId()) {
                    $hasEnfant = true;
                    break;
                }
            }

            if (!$hasEnfant) {
                $hasEnfant = $this->referenceHasEnfant($referenceEnfant, $references);
            } else {
                break;
            }
        }

        return $hasEnfant;
    }

    private function saveReferencesHaveEntities($entityType, $referencesEntitiesByEntityId)
    {
        foreach ($referencesEntitiesByEntityId as $entityId => $referencesEntities) {
            foreach ($referencesEntities as $referenceEntity) {
                $entityHasReference = $this->entityHasReferenceManager->createEmpty();
                $entityHasReference->setEntityId($entityId);
                $entityHasReference->setEntityType($entityType);
                $entityHasReference->setReference($referenceEntity['reference']);
                $entityHasReference->setPrimary($referenceEntity['primary']);
                $this->entityHasReferenceManager->save($entityHasReference);
            }
        }
    }
}

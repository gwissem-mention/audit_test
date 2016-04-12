<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Glossaire;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasGlossaireManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Enregistre le glossaire pour chaque publication.
 */
class Parse
{
    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasGlossaireManager EntityHasGlossaireManager
     */
    private $entityHasGlossaireManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\DomaineBundle\Manager\DomaineManager DomaineManager
     */
    private $domaineManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ContenuManager ContenuManager
     */
    private $contenuManager;


    /**
     * @var array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Glossaire
     */
    private static $GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID = null;


    /**
     * Constructeur.
     */
    public function __construct(Entity $entity, EntityHasGlossaireManager $entityHasGlossaireManager, ReferenceManager $referenceManager, DomaineManager $domaineManager, ObjetManager $objetManager, ContenuManager $contenuManager)
    {
        $this->entity = $entity;
        $this->entityHasGlossaireManager = $entityHasGlossaireManager;
        $this->referenceManager = $referenceManager;
        $this->domaineManager = $domaineManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
    }

    /**
     * Initialisation.
     */
    private function init()
    {
        $this->initGlossaire();
    }

    /**
     * Initialise le glossaire.
     */
    private function initGlossaire()
    {
        if (null === self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID) {
            self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID = [];

            foreach ($this->domaineManager->findAll() as $domaine) {
                $glossaireReferences = $this->referenceManager->findByDomaines([$domaine], true, null, null, null, null, true);
                usort($glossaireReferences, [$this, 'sortGlossaireReferences']);
                self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()] = $glossaireReferences;
            }
        }
    }

    /**
     * Tri les références de glossaire du mot le plus grand au plus petit.
     */
    private function sortGlossaireReferences(Reference $glossaireReference1, Reference $glossaireReference2)
    {
        $glossaireReference1Length = strlen($glossaireReference1->getSigleForGlossaire());
        $glossaireReference2Length = strlen($glossaireReference2->getSigleForGlossaire());

        return ($glossaireReference1Length > $glossaireReference2Length ? -1 : ($glossaireReference1Length < $glossaireReference2Length ? 1 : 0));
    }


    /**
     * Parse toutes les publications.
     */
    public function parseAndSaveAll()
    {
        $this->parseAndSaveObjets($this->objetManager->findBy([
            'etat' => $this->referenceManager->getEtatActif()
        ]));
        $this->parseAndSaveContenus($this->contenuManager->findAll());
    }

    /**
     * Parse et sauvegarde le glossaire des objets.
     *
     * @param array<\HopitalNumerique\ObjetBundle\Entity\Objet> Objets
     */
    private function parseAndSaveObjets($objets)
    {
        $this->init();

        foreach ($objets as $objet) {
            foreach ($this->entity->getDomainesByEntity($objet) as $domaine) {
                $foundSigles = $this->getFoundSiglesByText(self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()], strip_tags($objet->getResume()).' '.strip_tags($objet->getSynthese()));
                $this->saveEntityHasGlossaire(Entity::ENTITY_TYPE_PUBLICATION, $objet->getId(), $domaine, $foundSigles);
            }
        }
    }

    /**
     * Parse et sauvegarde le glossaire des contenus.
     *
     * @param array<\HopitalNumerique\ObjetBundle\Entity\Contenu> Contenus
     */
    private function parseAndSaveContenus($contenus)
    {
        $this->init();

        foreach ($contenus as $contenu) {
            foreach ($this->entity->getDomainesByEntity($contenu) as $domaine) {
                $foundSigles = $this->getFoundSiglesByText(self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()], strip_tags($contenu->getContenu()));
                $this->saveEntityHasGlossaire(Entity::ENTITY_TYPE_INFRADOC, $contenu->getId(), $domaine, $foundSigles);
            }
        }
    }

    /**
     * Parse et enregistre le glossaire de l'entité.
     *
     * @param objet $entity Entité
     */
    public function parseAndSaveEntity($entity)
    {
        switch ($this->entity->getEntityType($entity)) {
            case Entity::ENTITY_TYPE_PUBLICATION:
                $this->parseAndSaveObjets([$entity]);
                break;
            case Entity::ENTITY_TYPE_INFRADOC:
                $this->parseAndSaveContenus([$entity]);
                break;
            default:
                throw new \Exception('Entité non parsable pour le glossaire.');
        }
    }

    /**
     * Retourne la liste des sigles trouvés dans un texte.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\Reference> $glossaireReferences Références du glossaire à rechercher
     * @param string $text Texte
     */
    private function getFoundSiglesByText(array $glossaireReferences, $text)
    {
        $foundSigles = [];
        /**
         * Positions des sigles trouvés. Si on trouve un autre mot (plus petit) commençant au même endroit, on ne le prend pas.
         * @todo Fonctionnement repris de l'ancien, l'idéal serait de ne pas prendre le mot s'il est compris dans un autre et non uniquement s'il commence au même endroit.
         */
        $siglePositions = [];

        foreach ($glossaireReferences as $glossaireReference) {
            $siglePattern = '|'.$glossaireReference->getSigleHtmlForGlossaire().'|'.($glossaireReference->isCasseSensible() ? '' : 'i');
            preg_match_all($siglePattern, $text, $sigleMatches, PREG_OFFSET_CAPTURE);

            foreach ($sigleMatches[0] as $sigleMatch) {
                if (!in_array($sigleMatch[1], $siglePositions)) {
                    $foundSigles[] = $glossaireReference->getId();
                    $siglePositions[] = $sigleMatch[1];
                }
            }
        }

        return array_unique($foundSigles);
    }

    /**
     * Enregistre en base le glossaire si existant de l'entité.
     *
     * @param string                                         $entityType Type d'entité
     * @param integer                                        $entityId   ID de l'entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine    Domaine
     * @param array                                          $glossaire  Références trouvées
     */
    private function saveEntityHasGlossaire($entityType, $entityId, Domaine $domaine, array $glossaire)
    {
        $entityHasGlossaire = $this->entityHasGlossaireManager->findOneBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
            'domaine' => $domaine
        ]);

        if (0 === count($glossaire)) {
            if (null !== $entityHasGlossaire) {
                $this->entityHasGlossaireManager->delete($entityHasGlossaire);
            }
            return;
        }

        if (null === $entityHasGlossaire) {
            $entityHasGlossaire = $this->entityHasGlossaireManager->createEmpty();
            $entityHasGlossaire->setEntityType($entityType);
            $entityHasGlossaire->setEntityId($entityId);
            $entityHasGlossaire->setDomaine($domaine);
        }
        $entityHasGlossaire->setReferences($glossaire);

        $this->entityHasGlossaireManager->save($entityHasGlossaire);
    }
}

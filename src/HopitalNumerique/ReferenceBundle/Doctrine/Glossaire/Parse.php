<?php

namespace HopitalNumerique\ReferenceBundle\Doctrine\Glossaire;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasGlossaireManager;

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
     * @var EntityHasGlossaireManager EntityHasGlossaireManager
     */
    private $entityHasGlossaireManager;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var DomaineManager DomaineManager
     */
    private $domaineManager;

    /**
     * @var ObjetManager ObjetManager
     */
    private $objetManager;

    /**
     * @var ContenuManager ContenuManager
     */
    private $contenuManager;

    /**
     * @var CurrentDomaine $currentDomain
     */
    private $currentDomain;

    /**
     * @var Reference[] Glossaire
     */
    private static $GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID = null;

    /**
     * Constructeur.
     *
     * @param Entity                    $entity
     * @param EntityHasGlossaireManager $entityHasGlossaireManager
     * @param ReferenceManager          $referenceManager
     * @param DomaineManager            $domaineManager
     * @param ObjetManager              $objetManager
     * @param ContenuManager            $contenuManager
     * @param CurrentDomaine            $currentDomain
     */
    public function __construct(
        Entity $entity,
        EntityHasGlossaireManager $entityHasGlossaireManager,
        ReferenceManager $referenceManager,
        DomaineManager $domaineManager,
        ObjetManager $objetManager,
        ContenuManager $contenuManager,
        CurrentDomaine $currentDomain
    ) {
        $this->entity = $entity;
        $this->entityHasGlossaireManager = $entityHasGlossaireManager;
        $this->referenceManager = $referenceManager;
        $this->domaineManager = $domaineManager;
        $this->objetManager = $objetManager;
        $this->contenuManager = $contenuManager;
        $this->currentDomain = $currentDomain;
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
                $glossaireReferences = $this->referenceManager->findByDomaines(
                    [$domaine],
                    true,
                    null,
                    null,
                    null,
                    null,
                    true
                );
                usort($glossaireReferences, [$this, 'sortGlossaireReferences']);
                self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()] = $glossaireReferences;
            }
        }
    }

    /**
     * @param Domaine $domain
     *
     * @return array
     */
    private function getReferencesByDomain(Domaine $domain)
    {
        if (is_null(self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID)) {
            $this->initGlossaire();
        }

        return self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domain->getId()];
    }

    /**
     * Tri les références de glossaire du mot le plus grand au plus petit.
     *
     * @param Reference $glossaireReference1
     * @param Reference $glossaireReference2
     *
     * @return int
     */
    private function sortGlossaireReferences(Reference $glossaireReference1, Reference $glossaireReference2)
    {
        $glossaireReference1Length = strlen($glossaireReference1->getSigleForGlossaire());
        $glossaireReference2Length = strlen($glossaireReference2->getSigleForGlossaire());

        return $glossaireReference1Length > $glossaireReference2Length
            ? -1
            : ($glossaireReference1Length < $glossaireReference2Length
                ? 1
                : 0
            )
        ;
    }

    /**
     * Parse toutes les publications.
     */
    public function parseAndSaveAll()
    {
        $this->parseAndSaveObjets($this->objetManager->findBy([
            'etat' => $this->referenceManager->getEtatActif(),
        ]));
        $this->parseAndSaveContenus($this->contenuManager->findAll());
    }

    /**
     * Parse et sauvegarde le glossaire des objets.
     *
     * @param Objet[] Objets
     */
    private function parseAndSaveObjets($objets)
    {
        $this->init();
        $domaine = $this->currentDomain->get();

        foreach ($objets as $objet) {
            $foundSigles = $this->getFoundSiglesByText(
                self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()],
                strip_tags($objet->getResume()) . ' ' . strip_tags($objet->getSynthese())
            );
            $this->saveEntityHasGlossaire(Entity::ENTITY_TYPE_OBJET, $objet->getId(), $domaine, $foundSigles);
        }
    }

    /**
     * Parse et sauvegarde le glossaire des contenus.
     *
     * @param Contenu[] Contenus
     */
    private function parseAndSaveContenus($contenus)
    {
        $this->init();
        $domaine = $this->currentDomain->get();

        foreach ($contenus as $contenu) {
            $foundSigles = $this->getFoundSiglesByText(
                self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domaine->getId()],
                strip_tags($contenu->getContenu())
            );
            $this->saveEntityHasGlossaire(Entity::ENTITY_TYPE_CONTENU, $contenu->getId(), $domaine, $foundSigles);
        }
    }

    /**
     * Parse et sauvegarde le glossaire des autodiags.
     *
     * @param Autodiag[] $autodiags
     */
    private function parseAndSaveAutodiags($autodiags)
    {
        $this->init();

        $fields = [
            'autodiag' => [
                'instructions',
            ],
            'chapter' => [
                'title',
                'extendedLabel',
                'additionalDescription',
                'description',
            ],
            'questions' => [
                'label',
                'additionalDescription',
                'description',
            ]
        ];

        $domaine = $this->currentDomain->get();

        foreach ($autodiags as $autodiag) {
            $foundSigles = [];
            /** @var Autodiag\Container\Chapter $chapter */
            foreach ($autodiag->getChapters() as $chapter) {
                foreach ($fields['autodiag'] as $field) {
                    $foundSigles = array_merge($foundSigles, $this->getFoundedSigles($domaine, $autodiag, $field));
                }

                foreach ($fields['chapter'] as $field) {
                    $foundSigles = array_merge($foundSigles, $this->getFoundedSigles($domaine, $chapter, $field));
                }
            }

            foreach ($autodiag->getAttributes() as $attribute) {
                foreach ($fields['questions'] as $field) {
                    $foundSigles = array_merge($foundSigles, $this->getFoundedSigles($domaine, $attribute, $field));
                }
            }

            array_unique($foundSigles);

            $this->saveEntityHasGlossaire(Entity::ENTITY_TYPE_AUTODIAG, $autodiag->getId(), $domaine, $foundSigles);
        }
    }

    /**
     * @param Domaine $domain
     * @param $object
     * @param string $field
     *
     * @return array
     */
    private function getFoundedSigles(Domaine $domain, $object, $field)
    {
        $propertyAccessor = new PropertyAccessor();

        return $this->getFoundSiglesByText(
            self::$GLOSSAIRE_REFERENCES_GROUPED_BY_DOMAINE_ID[$domain->getId()],
            strip_tags($propertyAccessor->getValue($object, $field))
        );
    }

    /**
     * Parse et enregistre le glossaire de l'entité.
     *
     * @param $entity
     *
     * @throws \Exception
     */
    public function parseAndSaveEntity($entity)
    {
        switch ($this->entity->getEntityType($entity)) {
            case Entity::ENTITY_TYPE_OBJET:
                $this->parseAndSaveObjets([$entity]);
                break;
            case Entity::ENTITY_TYPE_CONTENU:
                $this->parseAndSaveContenus([$entity]);
                break;
            case Entity::ENTITY_TYPE_AUTODIAG:
                $this->parseAndSaveAutodiags([$entity]);
                break;
            default:
                throw new \Exception('Entité non parsable pour le glossaire.');
        }
    }

    /**
     * Retourne la liste des sigles trouvés dans un texte.
     *
     * @param Domaine $domain
     * @param string $text
     *
     * @return array
     */
    public function getFoundSiglesByDomainAndText(Domaine $domain, $text)
    {
        return $this->getFoundSiglesByText($this->getReferencesByDomain($domain), $text, true);
    }

    /**
     * Retourne la liste des sigles trouvés dans un texte.
     *
     * @param Reference[] $glossaireReferences Références du glossaire à rechercher
     * @param string      $text                Texte
     *
     * @return array
     */
    private function getFoundSiglesByText(array $glossaireReferences, $text, $provideEntity = false)
    {
        $foundSigles = [];
        /**
         * Positions des sigles trouvés.
         * Si on trouve un autre mot (plus petit) commençant au même endroit, on ne le prend pas.
         *
         * @todo Fonctionnement repris de l'ancien, l'idéal serait de ne pas prendre le mot s'il
         * est compris dans un autre et non uniquement s'il commence au même endroit.
         */
        $siglePositions = [];

        foreach ($glossaireReferences as $glossaireReference) {
            $siglePattern = '|'
                . $glossaireReference->getSigleHtmlForGlossaire()
                . '|'
                . ($glossaireReference->isCasseSensible() ? '' : 'i')
            ;
            preg_match_all($siglePattern, $text, $sigleMatches, PREG_OFFSET_CAPTURE);

            foreach ($sigleMatches[0] as $sigleMatch) {
                if (!in_array($sigleMatch[1], $siglePositions)) {
                    $foundSigles[] = $provideEntity ? $glossaireReference : $glossaireReference->getId();
                    $siglePositions[] = $sigleMatch[1];
                }
            }
        }

        return array_unique($foundSigles);
    }

    /**
     * Enregistre en base le glossaire si existant de l'entité.
     *
     * @param int     $entityType Type d'entité
     * @param int     $entityId   ID de l'entité
     * @param Domaine $domaine    Domaine
     * @param array   $glossaire  Références trouvées
     */
    private function saveEntityHasGlossaire($entityType, $entityId, Domaine $domaine, array $glossaire)
    {
        $entityHasGlossaire = $this->entityHasGlossaireManager->findOneBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
            'domaine' => $domaine,
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

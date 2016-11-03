<?php

namespace HopitalNumerique\PublicationBundle\Service;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Filesystem\Filesystem;

class SuggestionConverter
{
    /** @var ReferenceManager $referenceManager */
    protected $referenceManager;

    /** @var EntityHasReferenceManager $entityHasReference */
    protected $entityHasReference;

    /** @var Filesystem $fileSystem */
    protected $fileSystem;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var ObjetManager $objetManager */
    protected $objetManager;

    public function __construct(
        ReferenceManager $referenceManager,
        EntityHasReferenceManager $entityHasReferenceManager,
        ObjetManager $objetManager,
        Filesystem $filesystem,
        EntityManager $entityManager
    ) {
        $this->referenceManager = $referenceManager;
        $this->entityHasReference = $entityHasReferenceManager;
        $this->objetManager = $objetManager;
        $this->fileSystem = $filesystem;
        $this->entityManager = $entityManager;
    }


    /**
     * Converts a Suggestion entity to an Objet entity
     *
     * @param Suggestion $suggestion
     *
     * @return Objet
     */
    public function suggestionConverter(Suggestion $suggestion)
    {
        $objet = new Objet();

        $objet->setTitre($suggestion->getTitle());
        $objet->setAlias(self::setUniqueAlias($suggestion->getTitle()));
        $objet->setSource($suggestion->getLink());
        $objet->setSynthese($suggestion->getSynthesis());
        $objet->setResume($suggestion->getSummary());
        $objet->setPath($suggestion->getPath());
        $objet->setAlaune(false);
        $objet->setDateCreation($suggestion->getCreationDate());
        $objet->setPublicationPlusConsulte(false);
        $objet->setEtat($this->referenceManager->getEtatActif());
        $objet->addType($this->referenceManager->getCategorieTemoignage());
        $objet->setDomaines($suggestion->getDomains());
        $objet->setVignette('');

        $suggestionFileLocation = $suggestion->getAbsolutePath();
        $objetFileDestination = $objet->getUploadRootDir() . '/' . $suggestion->getPath();
        if (file_exists($suggestionFileLocation)) {
            $this->fileSystem->copy($suggestionFileLocation, $objetFileDestination);
        }

        $this->entityManager->persist($objet);
        $this->entityManager->flush();

        $suggestionReferences = $this->entityHasReference
            ->findByEntityTypeAndEntityIdAndDomaines(
                Entity::ENTITY_TYPE_SUGGESTION,
                $suggestion->getId(),
                $suggestion->getDomains()
            )
        ;

        /** @var EntityHasReference $reference */
        foreach ($suggestionReferences as $reference) {
            $objetReference = new EntityHasReference();
            $objetReference->setEntityId($objet->getId());
            $objetReference->setEntityType(Entity::ENTITY_TYPE_OBJET);
            $objetReference->setReference($reference->getReference());
            $objetReference->setPrimary(false);
            $this->entityManager->persist($objetReference);
        }

        $this->entityManager->flush();

        return $objet;
    }

    private function setUniqueAlias($text)
    {
        $alias = $this->slugify($text);
        $uniqueAlias = $alias;
        $ind = 1;

        while (count($this->objetManager->findBy(['alias' => $uniqueAlias])) > 0) {
            $ind++;
            $uniqueAlias = $alias . '-' . $ind;
        }

        return $uniqueAlias;
    }


    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

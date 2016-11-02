<?php

namespace HopitalNumerique\PublicationBundle\Service;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Entity\Suggestion;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use Symfony\Component\Filesystem\Filesystem;

class SuggestionConverter
{
    /** @var ReferenceManager $referenceManager */
    protected $referenceManager;

    /** @var Filesystem $fileSystem */
    protected $fileSystem;

    public function __construct(ReferenceManager $referenceManager, Filesystem $filesystem)
    {
        $this->referenceManager = $referenceManager;
        $this->fileSystem = $filesystem;
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
        $objet->setAlias(self::slugify($suggestion->getTitle()));
        $objet->setDomaines($suggestion->getDomains());
        $objet->setEtat($this->referenceManager->getEtatActif());
        $objet->setSource($suggestion->getLink());
        //@TODO: pas avec ID
        $objet->addType($this->referenceManager->findOneById(176));

        $objet->setPath($suggestion->getPath());
        $suggestionFileLocation = $suggestion->getAbsolutePath();
        $objetFileDestination = $objet->getUploadRootDir() . '/' . $suggestion->getPath();
        $this->fileSystem->copy($suggestionFileLocation, $objetFileDestination);


        $objet->setDateCreation($suggestion->getCreationDate());
        $objet->setResume($suggestion->getSummary());
        $objet->setSynthese($suggestion->getSynthesis());

        return $objet;
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

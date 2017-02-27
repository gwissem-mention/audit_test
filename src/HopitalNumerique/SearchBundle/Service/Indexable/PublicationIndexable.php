<?php

namespace HopitalNumerique\SearchBundle\Service\Indexable;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Publication type indexable.
 * This class is responsible of saying if a Publication can be indexable or not
 */
class PublicationIndexable
{
    /**
     * @var string
     */
    protected $domaineSlug;

    /**
     * PublicationIndexable constructor.
     *
     * @param string $domaineSlug
     */
    public function __construct($domaineSlug)
    {
        $this->domaineSlug = $domaineSlug;
    }

    /**
     * Check if $object is indexable
     *
     * @param Objet $object
     * @return bool
     */
    public function isObjectIndexable(Objet $object)
    {
        return !$object->getDomaines()
            ->map(function (Domaine $domaine) {
                return $domaine->getSlug();
            })
            ->filter(function ($slug) {
                return $slug === $this->domaineSlug;
            })
            ->isEmpty()
        ;
    }

    /**
     * Check if $content is indexable
     *
     * @param Contenu $content
     * @return bool
     */
    public function isContentIndexable(Contenu $content)
    {
        return !$content->getDomaines()
            ->map(function (Domaine $domaine) {
                return $domaine->getSlug();
            })
            ->filter(function ($slug) {
                return $slug === $this->domaineSlug;
            })
            ->isEmpty()
        ;
    }
}

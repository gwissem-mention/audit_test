<?php

namespace HopitalNumerique\RechercheParcoursBundle\DTO\Search;

class ProductionDTO
{
    /**
     * @var array $types
     */
    public $types = [];

    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $source
     */
    public $source;

    /**
     * @var string $subTitle
     */
    public $subTitle;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var array $relatedRisks
     */
    public $relatedRisks = [];

    /**
     * @var array $relatedHotPoints
     */
    public $relatedHotPoints = [];

    /**
     * @var array $relatedProductions
     */
    public $relatedProductions = [];

    /**
     * @var string $directLink
     */
    public $directLink;

    /**
     * ProductionDTO constructor.
     *
     * @param array $types
     * @param string $title
     * @param string $description
     * @param string $directLink
     * @param string $subTitle
     * @param string $source
     */
    public function __construct($types, $title, $description, $directLink, $subTitle = null, $source = null)
    {
        $this->types = $types;
        $this->title = $title;
        $this->source = $source;
        $this->subTitle = $subTitle;
        $this->description = $description;
        $this->directLink = $directLink;
    }


}

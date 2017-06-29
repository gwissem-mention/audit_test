<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\Contenu as ContenuModel;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Repository\ContenuRepository;

class ContentFactory extends Factory
{
    /**
     * @var ContenuRepository $contentRepository
     */
    protected $contentRepository;

    /**
     * ContentFactory constructor.
     *
     * @param ContenuRepository $contentRepository
     */
    public function __construct(ContenuRepository $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::CONTENT_TYPE;
    }

    /**
     * @param $content
     *
     * @return ContenuModel
     */
    public function build($content)
    {
        return new ContenuModel($content);
    }

    /**
     * @param $itemIds
     *
     * @return Contenu[]
     */
    public function getMultiple($itemIds)
    {
        return $this->contentRepository->findByIdsWithJoin($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|Contenu
     */
    public function get($itemId)
    {
        return $this->contentRepository->findByIdWithJoin($itemId);
    }
}

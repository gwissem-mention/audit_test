<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\AutodiagChapter;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\ContainerRepository;

class AutodiagChapterFactory extends Factory
{
    /**
     * @var ContainerRepository $containerRepository
     */
    protected $containerRepository;

    /**
     * AutodiagChapterFactory constructor.
     *
     * @param ContainerRepository $containerRepository
     */
    public function __construct(ContainerRepository $containerRepository)
    {
        $this->containerRepository = $containerRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::AUTODIAG_CHAPTER_TYPE;
    }

    /**
     * @param $content
     *
     * @return AutodiagChapter
     */
    public function build($content)
    {
        return new AutodiagChapter($content);
    }

    /**
     * @param $itemIds
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container[]
     */
    public function getMultiple($itemIds)
    {
        return $this->containerRepository->findByIdsWithJoin($itemIds);
    }

    /**
     * @param $itemId
     *
     * @return null|Chapter
     */
    public function get($itemId)
    {
        return $this->containerRepository->findByIdWithJoin($itemId);
    }
}

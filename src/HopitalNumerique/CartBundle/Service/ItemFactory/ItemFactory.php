<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory;

use HopitalNumerique\AutodiagBundle\Repository\Autodiag\ContainerRepository;
use HopitalNumerique\CartBundle\Model\Item\AutodiagChapter;
use HopitalNumerique\CartBundle\Model\Item\CDPGroup;
use HopitalNumerique\CartBundle\Model\Item\ForumTopic;
use HopitalNumerique\CartBundle\Entity\Item as ItemEntity;
use HopitalNumerique\CartBundle\Model\Item\Objet;
use HopitalNumerique\CartBundle\Model\Item\Contenu;
use HopitalNumerique\CartBundle\Service\ItemFactory\Factory\Factory;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository;
use HopitalNumerique\CartBundle\Model\Item\Person;
use HopitalNumerique\ForumBundle\Repository\TopicRepository;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\CartBundle\Model\Item\Item;
use HopitalNumerique\ObjetBundle\Repository\ContenuRepository;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Symfony\Component\Translation\TranslatorInterface;

class ItemFactory
{

    /**
     * @var Factory[] $factories
     */
    protected $factories;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * ItemFactory constructor.
     *
     * @param TranslatorInterface $translatorInterface
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param Factory $factory
     */
    public function addFactory(Factory $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * @param ItemEntity $item
     *
     * @return Item
     */
    public function build(ItemEntity $item)
    {
        if (is_null($obj = $this->getItem($item))) {
            return null;
        }

        $objectTypeName = $this->translator->trans(sprintf('item.object_type.%s', $obj->getObjectTypeLabelSlug()), [], 'cart');
        $obj
            ->setItem($item)
            ->setObjectTypeName($objectTypeName)
        ;

        return $obj;
    }

    /**
     * @param ItemEntity $item
     *
     * @return Item|null
     */
    private function getItem(ItemEntity $item)
    {
        foreach ($this->factories as $factory) {
            if ($factory->getType() === $item->getObjectType()) {
                return $factory->getItem($item->getObjectId());
            }
        }

        return null;
    }

    /**
     * Prepare data
     * Avoid multiple queries
     *
     * @param ItemEntity[] $items
     */
    public function prepare($items)
    {
        $groupedItems = [];

        foreach ($items as $item) {
            if (!isset($groupedItems[$item->getObjectType()])) {
                $groupedItems[$item->getObjectType()] = [];
            }

            $groupedItems[$item->getObjectType()][] = $item->getObjectId();
        }

        foreach ($this->factories as $factory) {
            if (isset($groupedItems[$factory->getType()])) {
                $factory->prepare($groupedItems[$factory->getType()]);
            }
        }
    }
}

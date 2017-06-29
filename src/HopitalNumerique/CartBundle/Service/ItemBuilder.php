<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Model\Item\Item;
use HopitalNumerique\CartBundle\Repository\CartItemRepository;
use HopitalNumerique\CartBundle\Repository\ReportFactoryItemRepository;
use HopitalNumerique\CartBundle\Repository\ReportItemRepository;
use HopitalNumerique\CartBundle\Service\ItemFactory\ItemFactory;
use HopitalNumerique\UserBundle\Entity\User;

class ItemBuilder
{
    /**
     * @var CartItemRepository $cartItemRepository
     */
    protected $cartItemRepository;

    /**
     * @var ReportItemRepository $reportItemRepository
     */
    protected $reportItemRepository;

    /**
     * @var ReportFactoryItemRepository $reportFactoryItemRepository
     */
    protected $reportFactoryItemRepository;

    /**
     * @var ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * ItemFactory constructor.
     *
     * @param CartItemRepository $cartItemRepository
     * @param ReportItemRepository $reportItemRepository
     * @param ReportFactoryItemRepository $reportFactoryItemRepository
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        CartItemRepository $cartItemRepository,
        ReportItemRepository $reportItemRepository,
        ReportFactoryItemRepository $reportFactoryItemRepository,
        ItemFactory $itemFactory
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->reportItemRepository = $reportItemRepository;
        $this->reportFactoryItemRepository = $reportFactoryItemRepository;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @param User $user
     *
     * @return Item[]
     */
    public function buildCart(User $user)
    {
        $items = $this->cartItemRepository->findBy(
            ['owner' => $user],
            ['addedAt' => 'DESC']
        );

        $this->itemFactory->prepare($items);

        return $this->buildItems($items);
    }

    /**
     * @param User $user
     *
     * @return Item[]
     */
    public function buildPendingReport(User $user)
    {
        $items = $this->reportFactoryItemRepository->getStagingFactoryItemsForUser($user);

        return $this->buildItemsFactory($items);
    }

    public function buildForReport(Report $report)
    {
        $items = $this->reportItemRepository->getDisplayableItemsForReport($report);

        return $this->buildItems($items);
    }

    /**
     * @param Item[] $items
     *
     * @return array
     */
    private function buildItems($items)
    {
        $buildedItems = [];
        foreach ($items as $item) {
            if (!is_null($buildedItem = $this->itemFactory->build($item))) {
                $buildedItems[] = $buildedItem;
            }
        }

        return $buildedItems;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function buildItemsFactory($items)
    {
        $buildedItems = [];
        foreach ($items as $item) {
            if (!is_null($buildedItem = $this->itemFactory->build($item->getItem()))) {
                $buildedItems[] = $buildedItem;
            }
        }

        return $buildedItems;
    }

    /**
     * @param ReportFactory $reportFactory
     *
     * @return Item[]
     */
    public function buildForReportFactory(ReportFactory $reportFactory)
    {
        /** @var ReportFactoryItem[] $items */
        $items = $this->reportFactoryItemRepository->findBy([
            'reportFactory' => $reportFactory
        ], [
            'position' => 'ASC'
        ]);

        return $this->buildItemsFactory($items);
    }
}

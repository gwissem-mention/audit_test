<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\CartBundle\Repository\CartItemRepository;
use HopitalNumerique\CartBundle\Repository\ReportFactoryRepository;
use HopitalNumerique\CartBundle\Repository\ReportItemRepository;
use HopitalNumerique\CartBundle\Service\ItemFactory\ItemFactory;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

class AddCartItemsToReportFactoryCommandHandler
{
    /**
     * @var ReportItemRepository $reportItemRepository
     */
    protected $reportItemRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var ReportFactoryRepository $reportFactoryRepository
     */
    protected $reportFactoryRepository;

    /**
     * @var CartItemRepository $cartItemRepository
     */
    protected $cartItemRepository;

    /**
     * AddCartItemsToReportCommandHandler constructor.
     *
     * @param ReportFactoryRepository $reportFactoryRepository
     * @param ReportItemRepository $reportItemRepository
     * @param EntityManagerInterface $entityManager
     * @param ItemFactory $itemFactory
     * @param TranslatorInterface $translator
     * @param CartItemRepository $cartItemRepository
     */
    public function __construct(
        ReportFactoryRepository $reportFactoryRepository,
        ReportItemRepository $reportItemRepository,
        EntityManagerInterface $entityManager,
        ItemFactory $itemFactory,
        TranslatorInterface $translator,
        CartItemRepository $cartItemRepository
    ) {
        $this->reportFactoryRepository = $reportFactoryRepository;
        $this->reportItemRepository = $reportItemRepository;
        $this->entityManager = $entityManager;
        $this->itemFactory = $itemFactory;
        $this->translator = $translator;
        $this->cartItemRepository = $cartItemRepository;
    }

    /**
     * @param AddCartItemsToReportFactoryCommand $command
     */
    public function handle(AddCartItemsToReportFactoryCommand $command)
    {
        /** @var ReportFactory $reportFactory */
        $reportFactory = $command->reportFactory;
        $reportItems = [];
        if (is_null($reportFactory)) {
            if (is_null($reportFactory = $this->reportFactoryRepository->getStagingFactoryForUser($command->user))) {
                $reportFactory = new ReportFactory($command->user);
                $this->entityManager->persist($reportFactory);
            } else {
                $reportItems = $reportFactory->getReportItems();
            }
        }

        if (!is_null($reportFactory->getReport())) {
            $reportItems = $reportFactory->getReport()->getItems();
        }

        foreach ($command->items as $objectType => $objects) {
            foreach ($objects as $objectId) {
                $reportItem = $this->getReportItem($reportItems, $objectType, $objectId, $command->user);

                $this->createItemLink($reportItem, $reportFactory, $command->user);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param ReportItem[] $reportItems
     * @param string $objectType
     * @param string $objectId
     * @param Report|null $report
     *
     * @return ReportItem
     */
    private function getReportItem($reportItems, $objectType, $objectId, User $user)
    {
        foreach ($reportItems as $reportItem) {
            if ($reportItem->getObjectType() == $objectType && $reportItem->getObjectId() == $objectId) {
                return $reportItem;
            }
        }

        $cartItem = $this->cartItemRepository->findByObjectAndOwner($objectType, $objectId, $user);

        $reportItem = new ReportItem($objectType, $objectId, $cartItem->getDomain(), null);
        $this->entityManager->persist($reportItem);

        return $reportItem;
    }

    /**
     * @param ReportItem $reportItem
     * @param ReportFactory $reportFactory
     * @param User $user
     *
     * @return bool|ReportFactoryItem
     */
    private function createItemLink(ReportItem $reportItem, ReportFactory $reportFactory, User $user)
    {
        /** @var ReportFactoryItem $factoryItem */
        foreach ($reportFactory->getFactoryItems() as $factoryItem) {
            if ($factoryItem->getItem() === $reportItem) {
                return false;
            }
        }

        $reportFactoryItem = new ReportFactoryItem($reportFactory, $user, $reportItem,0);
        $this->entityManager->persist($reportFactoryItem);

        return $reportFactoryItem;
    }
}

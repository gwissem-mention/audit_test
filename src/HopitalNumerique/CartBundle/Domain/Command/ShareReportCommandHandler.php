<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CartBundle\Entity\Item\CartItem;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Event\ReportSharedEvent;
use HopitalNumerique\CartBundle\Events;
use HopitalNumerique\CartBundle\Exception\ReportAlreadySharedToUserException;
use HopitalNumerique\CartBundle\Repository\CartItemRepository;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ReportGenerator;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ShareReportCommandHandler
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var CartItemRepository $cartItemRepository
     */
    protected $cartItemRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var ReportGenerator
     */
    protected $reportGenerator;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * ShareReportCommandHandler constructor.
     *
     * @param UserRepository           $userRepository
     * @param CartItemRepository       $cartItemRepository
     * @param EntityManagerInterface   $entityManager
     * @param ReportGenerator          $reportGenerator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UserRepository $userRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager,
        ReportGenerator $reportGenerator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->reportGenerator = $reportGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ShareReportCommand $command
     * @throws NoResultException
     * @throws ReportAlreadySharedToUserException
     */
    public function handle(ShareReportCommand $command)
    {
        if (is_null($user = $this->userRepository->findUserByEmail($command->targetEmail)) || $user === $command->user) {
            throw new NoResultException();
        }

        if ($command->type !== ReportSharing::TYPE_COPY && $this->isReportAlreadySharedToUser($command->report, $user, $command->type)) {
            throw new ReportAlreadySharedToUserException();
        }

        $reportSharing = new ReportSharing($command->user, $user, $command->report, $command->type);
        $this->entityManager->persist($reportSharing);

        $this->addItemsToTargetCart($command->report->getItems(), $user);

        if ($command->type === ReportSharing::TYPE_COPY) {
            $copiedReport = clone $command->report;
            $copiedReport->setOwner($user);
            $copiedReport->setSharedBy($reportSharing);
            $this->entityManager->persist($copiedReport);
        }

        $this->entityManager->flush();

        if ($command->type === ReportSharing::TYPE_COPY) {
            $this->reportGenerator->generate($copiedReport);
        }

        /**
         * Fire REPORT_SHARED or REPORT_COPIED event.
         */
        if (ReportSharing::TYPE_SHARE === $command->type) {
            $eventCode = Events::REPORT_SHARED;
        } else {
            $eventCode = Events::REPORT_COPIED;
        }

        $event = new ReportSharedEvent($command->report, $command->user, $user);
        $this->eventDispatcher->dispatch($eventCode, $event);
    }

    /**
     * @param ReportItem[] $items
     * @param User $target
     */
    private function addItemsToTargetCart($items, User $target)
    {
        $cartItems = new ArrayCollection($this->cartItemRepository->findByOwner($target));

        foreach ($items as $item) {
            if ($cartItems->filter(function (CartItem $cartItem) use ($item) {
                return $cartItem->getObjectId() == $item->getObjectId() && $cartItem->getObjectType() == $item->getObjectType();
            })->count() === 0) {
                $cartItem = new CartItem($item->getObjectType(), $item->getObjectId(), $target, $item->getDomain());
                $this->entityManager->persist($cartItem);
            }
        }
    }

    /**
     * @param Report $report
     * @param User $user
     * @param string $type
     *
     * @return bool
     */
    private function isReportAlreadySharedToUser(Report $report, User $user, $type)
    {
        return $report->getShares()->filter(function (ReportSharing $reportSharing) use ($user, $type) {
            return $reportSharing->getTarget() === $user && $reportSharing->getType() === $type;
        })->count() > 0 || $report->getOwner() === $user;
    }
}

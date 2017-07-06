<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RemoveGuidedSearchHandler
 */
class RemoveGuidedSearchHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * ShareGuidedSearchHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param RemoveGuidedSearchCommand $command
     */
    public function handle(RemoveGuidedSearchCommand $command)
    {
        if ($command->guidedSearch->isOwner($command->user)) {
            $this->entityManager->remove($command->guidedSearch);

            $this->entityManager->flush();
        } else {
            $command->guidedSearch->removeShare($command->user);

            $this->entityManager->flush();
        }
    }
}

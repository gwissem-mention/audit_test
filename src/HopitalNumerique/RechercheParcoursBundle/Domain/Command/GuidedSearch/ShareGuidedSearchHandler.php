<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Exception\GuidedSearch\Share\UserNotFoundException;
use HopitalNumerique\RechercheParcoursBundle\Exception\GuidedSearch\Share\AlreadySharedException;

/**
 * Class ShareGuidedSearchHandler
 */
class ShareGuidedSearchHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * ShareGuidedSearchHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ShareGuidedSearchCommand $command
     * @throws AlreadySharedException
     * @throws UserNotFoundException
     */
    public function handle(ShareGuidedSearchCommand $command)
    {
        /** @var User $targetUser */
        if (is_null($targetUser = $this->userRepository->findOneBy(['email' => $command->email])) || $command->user === $targetUser) {
            throw new UserNotFoundException('User not founded with email provided.');
        }

        if ($command->guidedSearch->getShares()->contains($targetUser)) {
            throw new AlreadySharedException(sprintf('Guided search is already shared with target user %s.', $command->email));
        }

        $guidedSearchDomains = $command->guidedSearch
            ->getGuidedSearchReference()
            ->getRecherchesParcoursGestion()
            ->getDomaines()
        ;

        // Checks if the user has at least one domain of the autodiag
        if ($guidedSearchDomains->count() > 0) {
            $founded = false;
            foreach ($guidedSearchDomains as $domain) {
                if ($targetUser->hasDomaine($domain)) {
                    $founded = true;
                    break;
                }
            }

            // Adds the first domain of the guided search if a common domain isn't found.
            if (!$founded) {
                $targetUser->addDomaine($guidedSearchDomains->first());
            }
        }

        $command->guidedSearch->addShare($targetUser);

        $this->fillRiskAnalysis($command, $targetUser);

        $this->entityManager->flush();
    }

    /**
     * @param ShareGuidedSearchCommand $command
     * @param User $targetUser
     */
    private function fillRiskAnalysis(ShareGuidedSearchCommand $command, User $targetUser)
    {
        if ($command->initialData === false) {
            return;
        }

        /** @var GuidedSearchStep $step */
        foreach ($command->guidedSearch->getSteps() as $step) {
            $riskAnalysis = $step->getRisksAnalysis()->filter(function (RiskAnalysis $riskAnalysis) use ($command) {
                return $command->user === $riskAnalysis->getOwner();
            });

            foreach ($riskAnalysis as $riskAnalyse) {
                /** @var RiskAnalysis $filledRiskAnalyse */
                $filledRiskAnalyse = clone $riskAnalyse;
                $filledRiskAnalyse->setOwner($targetUser);

                $this->entityManager->persist($filledRiskAnalyse);
            }
        }
    }
}

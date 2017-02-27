<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SynthesisRemover
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SynthesisRepository
     */
    protected $synthesisRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    const SYNTHESIS_REMOVED = 1;
    const SHARE_REMOVED = 2;
    const SHARE_NOT_FOUND = 3;

    public function __construct(EntityManager $entityManager, SynthesisRepository $synthesisRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->synthesisRepository = $synthesisRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Supprime la synthèse et la entries associées.
     *
     * @param Synthesis $synthesis
     * @param User      $user
     *
     * @return bool
     */
    public function removeSynthesis(Synthesis $synthesis, User $user)
    {
        // Si la synthèse n'appartient pas à l'utilisateur
        if ($this->authorizationChecker->isGranted('delete', $synthesis)) {
            $this->entityManager->remove($synthesis);

            // Si les entries d'une synthèse ne sont pas utilisées par d'autres synthèses on les supprime
            foreach ($synthesis->getEntries() as $entry) {
                /** @var AutodiagEntry $entry */
                $relatedSyntheses = $this->synthesisRepository->findSynthesesByEntry($entry);

                if (count($relatedSyntheses) == 1) {
                    $this->entityManager->remove($entry);
                } else {
                    $entry->removeSynthesis($synthesis);
                }
            }

            $this->entityManager->flush();

            return self::SYNTHESIS_REMOVED;
        }

        return null;
    }

    public function removeShare(Synthesis $synthesis, User $user)
    {
        $found = false;
        // Can read but not delete = delete share
        if ($this->authorizationChecker->isGranted('read', $synthesis)) {
            foreach ($synthesis->getShares() as $share) {
                if ($share == $user) {
                    $synthesis->removeShare($user);
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->entityManager->flush();

                return self::SHARE_REMOVED;
            }

            return self::SHARE_NOT_FOUND;
        }

        return null;
    }
}

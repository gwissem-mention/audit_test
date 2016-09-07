<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository;
use HopitalNumerique\UserBundle\Entity\User;

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

    const SYNTHESIS_REMOVED = 1;
    const SHARE_REMOVED = 2;
    const SHARE_NOT_FOUND = 3;

    public function __construct(EntityManager $entityManager, SynthesisRepository $synthesisRepository)
    {
        $this->entityManager = $entityManager;
        $this->synthesisRepository = $synthesisRepository;
    }

    /**
     * Supprime la synthèse et la entries associées
     *
     * @param Synthesis $synthesis
     * @param User $user
     * @return boolean
     */
    public function removeSynthesis(Synthesis $synthesis, User $user)
    {
        $found = false;

        // Si la synthèse n'appartient pas à l'utilisateur
        if ($synthesis->getUser() != $user) {
            foreach ($synthesis->getShares() as $share) {
                if ($share == $user) {
                    $synthesis->removeShare($user);
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->entityManager->flush();

                return SynthesisRemover::SHARE_REMOVED;
            } else {
                return SynthesisRemover::SHARE_NOT_FOUND;
            }
        } // Si la synthèse appartient à l'utilisateur
        else {
            $this->entityManager->remove($synthesis);

            // Si les entries d'une synthèse ne sont pas utilisées par d'autres synthèses on les supprime
            foreach ($synthesis->getEntries() as $entry) {
                $relatedSyntheses = $this->synthesisRepository->findSynthesesByEntry($entry);

                if (count($relatedSyntheses) == 1) {
                    $this->entityManager->remove($entry);
                }
            }

            $this->entityManager->flush();

            return SynthesisRemover::SYNTHESIS_REMOVED;
        }
    }
}


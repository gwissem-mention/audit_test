<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Entity\User;

class DataFormer
{
    /**
     * @var EntityManager
     */
    protected $manager;

    protected $completion;

    /**
     * DataFormer constructor.
     * @param $completion
     */
    public function __construct(EntityManager $manager, Completion $completion)
    {
        $this->manager = $manager;
        $this->completion = $completion;
    }

    public function getSynthesesByAutodiag(User $user)
    {
        $synthesisRepository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');

        $syntheses = $synthesisRepository->findByUserId($user->getId());

        $currentSynthesesByAutodiag = [];
        $validSynthesesByAutodiag = [];

        foreach ($syntheses as $synth) {
            /** @var \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synth */
            if ($synth->getValidatedAt() == null) {
                if (!array_key_exists($synth->getAutodiag()->getId(), $currentSynthesesByAutodiag)) {
                    $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                    ];
                }
                $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'name' => $synth->getName(),
                    'updated_at' => $synth->getUpdatedAt(),
                    'completion' => rand(0, 100) // $this->completion->getGlobalCompletion($synth),
                ];

            } else {
                if (!array_key_exists($synth->getAutodiag()->getId(), $validSynthesesByAutodiag)) {
                    $validSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                    ];
                }
                $validSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'name' => $synth->getName(),
                    'validated_at' => $synth->getValidatedAt(),
                    'share' => 'Toto'
                ];
            }
        }

        return ['currentSyntheses' => $currentSynthesesByAutodiag, 'validSyntheses' => $validSynthesesByAutodiag];
    }
}

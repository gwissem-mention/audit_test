<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Formatte les données des synthèses pour l'affichage dans la partie Mon Compte
 */
class DataFormer
{
    /**
     * @var EntityManager
     */
    protected $manager;

    protected $completion;

    /**
     * DataFormer constructor.
     * @param EntityManager $manager
     * @param Completion $completion
     */
    public function __construct(EntityManager $manager, Completion $completion)
    {
        $this->manager = $manager;
        $this->completion = $completion;
    }

    /**
     * Retourne un tableau composé de deux sous tableaux,
     * le premier contient les infos à afficher pour les synthèses non-validées
     * le second contient les infos à afficher pour les synthèses validées (avec les syntèses partagées)
     *
     * @param User $user
     * @return array
     */
    public function getSynthesesByAutodiag(User $user)
    {
        $synthesisRepository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');

        $syntheses = $synthesisRepository->findByUser($user);

        $currentSynthesesByAutodiag = [];
        $validSynthesesByAutodiag = [];

        foreach ($syntheses as $synth) {
            /** @var \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synth */

            // Si la synthèse n'est pas validée
            if ($synth->getValidatedAt() == null) {
                if (!array_key_exists($synth->getAutodiag()->getId(), $currentSynthesesByAutodiag)) {
                    $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                    ];
                }

                $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'id' => $synth->getId(),
                    'entryId' => count($synth->getEntries()) == 1 ? $synth->getEntries()[0]->getId() : null,
                    'synth' => $synth,
                    'name' => $synth->getName(),
                    'updated_at' => $synth->getUpdatedAt(),
                    'completion' => $this->completion->getCompletionRate($synth),
                ];
            } // Si la synthèse est validée
            else {
                if (!array_key_exists($synth->getAutodiag()->getId(), $validSynthesesByAutodiag)) {
                    $validSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                        'id' => $synth->getAutodiag()->getId(),
                        'synthesisAllowed' => $synth->getAutodiag()->isSynthesisAuthorized(),
                    ];
                }

                $validSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'id' => $synth->getId(),
                    'name' => $synth->getName(),
                    'validated_at' => $synth->getValidatedAt(),
                    'user' => $synth->getUser(),
                    'entries' => $synth->getEntries(),
                    'share' => array_map(function ($share) {
                        return $share->getPrenom() . ' ' . $share->getNom();
                    }, $synth->getShares()->toArray()),
                ];
            }
        }

        return [
            'currentSyntheses' => $currentSynthesesByAutodiag,
            'validSyntheses' => $validSynthesesByAutodiag,
        ];
    }
}

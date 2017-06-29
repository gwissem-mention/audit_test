<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Formatte les données des synthèses pour l'affichage dans la partie Mon Compte.
 */
class DataFormatter
{
    /**
     * @var EntityManager
     */
    protected $manager;

    protected $completion;

    /**
     * DataFormatter constructor.
     *
     * @param EntityManager $manager
     * @param Completion    $completion
     */
    public function __construct(EntityManager $manager, Completion $completion)
    {
        $this->manager = $manager;
        $this->completion = $completion;
    }

    /**
     * Retourne les synthèses ordonnées par autodiag.
     *
     * @param User         $user
     * @param Domaine|null $domaine
     *
     * @return array
     */
    public function getSynthesesOrderByAutodiag(User $user, Domaine $domaine = null)
    {
        $synthesisRepository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');

        $syntheses = $synthesisRepository->findByUser($user, $domaine);

        return $this->formatteSyntheses($syntheses);
    }

    /**
     * Retourne les synthèses formattées pour un autodiag.
     *
     * @param User         $user
     * @param Autodiag     $autodiag
     * @param Domaine|null $domaine
     *
     * @return array
     */
    public function getSynthesesByAutodiag(User $user, Autodiag $autodiag, Domaine $domaine = null)
    {
        $synthesisRepository = $this->manager->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');

        $syntheses = $synthesisRepository->findByUserAndAutodiag($user, $autodiag, $domaine);

        return $this->formatteSyntheses($syntheses);
    }

    /**
     * Retourne un tableau composé de deux sous tableaux,
     * le premier contient les infos à afficher pour les synthèses non-validées
     * le second contient les infos à afficher pour les synthèses validées (avec les syntèses partagées).
     *
     * @param array $syntheses
     *
     * @return array
     */
    public function formatteSyntheses(array $syntheses)
    {
        $currentSynthesesByAutodiag = [];
        $validSynthesesByAutodiag = [];

        /** @var Synthesis $synth */
        foreach ($syntheses as $synth) {
            $autodiagUpdateDate = $synth->getAutodiag()->getPublicUpdatedDate();

            // Si la synthèse n'est pas validée
            if ($synth->getValidatedAt() == null) {
                if (!array_key_exists($synth->getAutodiag()->getId(), $currentSynthesesByAutodiag)) {
                    $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                        'updated' => false,
                    ];
                }

                $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'id' => $synth->getId(),
                    'entryId' => count($synth->getEntries()) == 1 ? $synth->getEntries()[0]->getId() : null,
                    'synth' => $synth,
                    'name' => $synth->getName(),
                    'computing' => $synth->isComputing(),
                    'updated_at' => $synth->getUpdatedAt(),
                    'completion' => $synth->getCompletion(),
                ];
            } // Si la synthèse est validée
            else {
                if (!array_key_exists($synth->getAutodiag()->getId(), $validSynthesesByAutodiag)) {
                    $validSynthesesByAutodiag[$synth->getAutodiag()->getId()] = [
                        'syntheses' => [],
                        'name' => $synth->getAutodiag()->getTitle(),
                        'id' => $synth->getAutodiag()->getId(),
                        'synthesisAllowed' => $synth->getAutodiag()->isSynthesisAuthorized(),
                        'updated' => false,
                    ];
                }

                $validSynthesesByAutodiag[$synth->getAutodiag()->getId()]['syntheses'][$synth->getId()] = [
                    'id' => $synth->getId(),
                    'name' => $synth->getName(),
                    'validated_at' => $synth->getValidatedAt(),
                    'user' => $synth->getUser(),
                    'entries' => $synth->getEntries(),
                    'computing' => $synth->isComputing(),
                    'share' => array_map(function (User $share) {
                        return $share->getFirstname() . ' ' . $share->getLastname();
                    }, $synth->getShares()->toArray()),
                ];
            }

            if ($synth->getUpdatedAt() < $autodiagUpdateDate) {
                if (isset($currentSynthesesByAutodiag[$synth->getAutodiag()->getId()])) {
                    $currentSynthesesByAutodiag[$synth->getAutodiag()->getId()]['updated'] = true;
                }

                if (isset($validSynthesesByAutodiag[$synth->getAutodiag()->getId()])) {
                    $validSynthesesByAutodiag[$synth->getAutodiag()->getId()]['updated'] = true;
                }
            }
        }

        return [
            'currentSyntheses' => $currentSynthesesByAutodiag,
            'validSyntheses' => $validSynthesesByAutodiag,
        ];
    }
}

<?php

namespace HopitalNumerique\UserBundle\Service;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ShareMessageGenerator
 *
 * Generates a generic message for shared object for users
 */
class ShareMessageGenerator
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * ShareMessageGenerator constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param User[] $shares The list of shares.
     * @param User   $owner The owner of the target object.
     * @param User   $user The current user.
     *
     * @return string
     */
    public function getShareMessage($shares, User $owner, User $user)
    {
        $sharedWith = [];
        $sharedBy = null;
        $message = "";

        foreach ($shares as $share) {
            if ($share->getId() !== $owner->getId() && $share->getId() !== $owner->getId()) {
                $sharedWith[] = $share;
            }

            if ($owner->getId() !== $user->getId()) {
                $sharedBy = $owner;
            }

            if (null !== $sharedBy) {
                $message = $this->translator->trans(
                    'guided_search.shared_by',
                    ['%user%' => $sharedBy->getPrenomNom()],
                    'widget'
                );
            }
        }

        if (count($sharedWith) > 0) {
            $sharedWithString = implode(', ', array_map(function ($share) {
                return $share->getPrenomNom();
            }, $sharedWith));

            if (null !== $sharedBy) {
                $message .= $this->translator->trans(
                    'guided_search.with',
                    ['%users%' => $sharedWithString],
                    'widget'
                );
            } else {
                $message = $this->translator->trans(
                    'guided_search.shared_with',
                    ['%users%' => $sharedWithString],
                    'widget'
                );
            }
        }

        return $message;
    }
}

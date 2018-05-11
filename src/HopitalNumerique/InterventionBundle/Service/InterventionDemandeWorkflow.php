<?php

namespace HopitalNumerique\InterventionBundle\Service;

use HopitalNumerique\UserBundle\Entity\User;

class InterventionDemandeWorkflow
{
    /**
     * Allows to check if user's account informations are enough to ask intervention.
     *
     * @param User
     * @return bool
     */
    public function userCanAskIntervention(User $user)
    {
        // NOTE : No information required for role CMSI.
        // TODO : To validate
        if ($user->hasRoleCmsi()) {
            return true;
        }

        return (null !== $user->getProfileType() && (!is_null($user->getPhoneNumber()) || !is_null($user->getCellPhoneNumber())));
    }
}
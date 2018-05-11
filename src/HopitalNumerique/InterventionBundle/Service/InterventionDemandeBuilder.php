<?php

namespace HopitalNumerique\InterventionBundle\Service;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class InterventionDemandeBuilder
 * @package HopitalNumerique\InterventionBundle\Service
 */
class InterventionDemandeBuilder
{
    /**
     * @param User $user
     * @param User $ambassadeur
     * @param Objet|null $prod
     *
     * @return InterventionDemande
     */
    public function buildFromUser(User $user, User $ambassadeur, Objet $prod = null)
    {
        $interventionDemande = new InterventionDemande();

        if (null !== $user->getOrganization()) {
            if (!empty($user->getOrganization()->getAdresse())) {
                $interventionDemande->setDesiredLocationAddress($user->getOrganization()->getAdresse());
            } else {
                $interventionDemande->setDesiredLocationAddress($user->getOrganization()->getNom());
            }
            $interventionDemande->setDesiredLocationZipCode($user->getOrganization()->getCodepostal());
            $interventionDemande->setDesiredLocationCity($user->getOrganization()->getVille());
        }

        $interventionDemande->setEmail($user->getEmail());

        if (null !== $user->getPhoneNumber()) {
            $interventionDemande->setTelephone($user->getPhoneNumber());
        } elseif (null !== $user->getCellPhoneNumber()) {
            $interventionDemande->setTelephone($user->getCellPhoneNumber());
        }

        $interventionDemande->setAmbassadeur($ambassadeur);
        if ($prod != null) {
            $this->interventionDemande->addObjet($prod);
        }

        return $interventionDemande;
    }
}
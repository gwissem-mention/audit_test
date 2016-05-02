<?php
namespace HopitalNumerique\AccountBundle\Doctrine\Reference;

use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;

/**
 * Connexion entre certaines références et le compte utilisateur.
 */
class Contexte
{
    /**
     * @var \HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser ConnectedUser
     */
    private $connectedUser;


    /**
     * Constructeur.
     */
    public function __construct(ConnectedUser $connectedUser)
    {
        $this->connectedUser = $connectedUser;
    }


    /**
     * Retourne les ID des références du contexte utilisateur.
     *
     * @return array<integer> IDs des références
     */
    public function getReferenceIds()
    {
        $userContexteReferenceIds = [];

        if ($this->connectedUser->is()) {
            if (null !== $this->connectedUser->get()->getFonctionDansEtablissementSanteReferencement()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getFonctionDansEtablissementSanteReferencement()->getId();
            }
            if (null !== $this->connectedUser->get()->getProfilEtablissementSante()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getProfilEtablissementSante()->getId();
            }
            if (null !== $this->connectedUser->get()->getStatutEtablissementSante()) {
                $userContexteReferenceIds[] = $this->connectedUser->get()->getStatutEtablissementSante()->getId();
            }
            foreach ($this->connectedUser->get()->getTypeActivite() as $activiteType) {
                $userContexteReferenceIds[] = $activiteType->getId();
            }
        }

        return $userContexteReferenceIds;
    }
}

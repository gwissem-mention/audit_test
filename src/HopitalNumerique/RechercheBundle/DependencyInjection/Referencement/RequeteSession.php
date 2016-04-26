<?php
namespace HopitalNumerique\RechercheBundle\DependencyInjection\Referencement;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\RechercheBundle\Manager\RequeteManager;
use HopitalNumerique\UserBundle\DependencyInjection\ConnectedUser;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Enregistrement temporaire de la requête de recherche par référencement.
 */
class RequeteSession
{
    /**
     * @var string Préfixe de la session des références
     */
    const SESSION_REFERENCES_NAME = 'hnrecherche_referencement_requete_references';

    /**
     * @var string Préfixe de la session de la requete
     */
    const SESSION_REQUETE_NAME = 'hnrecherche_referencement_requete_requete';


    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\RechercheBundle\Manager\RequeteManager RequeteManager
     */
    private $requeteManager;


    /**
     * @var \HopitalNumerique\UserBundle\Entity\User|null User
     */
    private $user;

    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine Domaine courant
     */
    private $domaine;


    /**
     * Constructeur.
     */
    public function __construct(SessionInterface $session, ConnectedUser $connectedUser, CurrentDomaine $currentDomaine, RequeteManager $requeteManager)
    {
        $this->session = $session;
        $this->requeteManager = $requeteManager;

        $this->user = $connectedUser->get();
        $this->domaine = $currentDomaine->get();
    }


    /**
     * Retourne les IDs des références.
     *
     * @return array<integer> IDs des références
     */
    public function getReferenceIds()
    {
        return $this->session->get(self::SESSION_REFERENCES_NAME, []);
    }

    /**
     * Enregistre les références en session.
     *
     * @param array<integer> $referenceIds IDs des références
     */
    public function setReferenceIds(array $referenceIds)
    {
        $this->session->set(self::SESSION_REFERENCES_NAME, $referenceIds);
    }

    /**
     * Retourne la requête.
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\Requete|null Requête
     */
    public function getRequete()
    {
        $requeteId = $this->session->get(self::SESSION_REQUETE_NAME);

        if (null !== $requeteId) {
            return $this->requeteManager->findOneById($requeteId);
        }

        return null;
    }

    /**
     * Enregistre la requête en session.
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\Requete $requete Requête
     */
    private function setRequete(Requete $requete)
    {
        $this->session->set(self::SESSION_REQUETE_NAME, $requete->getId());
    }

    /**
     * Supprime la session.
     */
    public function remove()
    {
        $this->session->remove(self::SESSION_REFERENCES_NAME);
        $this->session->remove(self::SESSION_REQUETE_NAME);
    }

    /**
     * Enregistre la requête actuelle pour l'utilisateur connecté.
     */
    public function saveAsNewRequete(User $user = null)
    {
        $requeteUser = (null !== $user ? $user : $this->user);

        if (null !== $requeteUser) {
            $referenceIds = $this->getReferenceIds();

            if (count($referenceIds) > 0) {
                $requete = $this->requeteManager->createEmpty();
                $requete->setNom(Requete::DEFAULT_NOM);
                $requete->setIsDefault(true);
                $requete->setIsUserNotified(false);
                $requete->setRefs($referenceIds);
                $requete->setUser($requeteUser);
                $requete->setDomaine($this->domaine);
                $this->requeteManager->save($requete);
                $this->setRequete($requete);
            }
        }
    }

    /**
     * Enregistre la requête.
     */
    public function saveRequete(Requete $requete)
    {
        $requete->setRefs($this->getReferenceIds());
        $this->requeteManager->save($requete);
        $this->setRequete($requete);
    }
}

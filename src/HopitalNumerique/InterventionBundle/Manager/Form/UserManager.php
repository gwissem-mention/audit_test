<?php

/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Nodevo\AclBundle\Manager\AclManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;

/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 */
class UserManager
{
    /**
     * @var User L'utilisateur connecté
     */
    private $utilisateurConnecte;
    /**
     * @var Router Router de l'application
     */
    private $router;
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager Le manager de l'entité User
     */
    private $userManager;
    /**
     * @var AclManager Le manager de l'entité Acl
     */
    private $aclManager;
    /**
     * @var ReferenceManager Manager de Reference
     */
    private $referenceManager;
    /**
     * @var EtablissementManager Manager de Etablissement
     */
    private $etablissementManager;

    /**
     * Constructeur du manager gérant les formulaires utilisateurs.
     *
     * @param TokenStorage                                     $tokenStorage
     * @param Router                                           $router               Router de l'application
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager          Le manager de l'entité User
     * @param AclManager                                       $aclManager           Le manager de l'entité Acl
     * @param ReferenceManager                                 $referenceManager     Manager de Reference
     * @param EtablissementManager                             $etablissementManager Manager de Etablissement
     */
    public function __construct(
        TokenStorage $tokenStorage,
        Router $router,
        \HopitalNumerique\UserBundle\Manager\UserManager $userManager,
        AclManager $aclManager,
        ReferenceManager $referenceManager,
        EtablissementManager $etablissementManager
    ) {
        $this->router = $router;
        $this->userManager = $userManager;
        $this->aclManager = $aclManager;
        $this->referenceManager = $referenceManager;
        $this->etablissementManager = $etablissementManager;
        $this->tokenStorage = $tokenStorage;
        $this->utilisateurConnecte = $tokenStorage->getToken()->getUser();
    }

    /**
     * Retourne la liste des titres pour les listes de formulaire.
     *
     * @return array Liste des titres pour les listes de formulaire
     */
    public function getTitresChoices()
    {
        return $this->referenceManager->findByCode('TITRE');
    }

    /**
     * Retourne la liste des régions pour les listes de formulaire.
     *
     * @return array Liste des régions pour les listes de formulaire
     */
    public function getRegionsChoices()
    {
        return $this->referenceManager->findByCode('REGION');
    }

    /**
     * Retourne la liste des établissements pour les listes de formulaire.
     *
     * @return array Liste des établissements pour les listes de formulaire
     */
    public function getEtablissementsChoices()
    {
        return $this->etablissementManager->findAll();
    }

    /**
     * Retourne la liste des fonctions dans l'établissement de santé pour les listes de formulaire.
     *
     * @return array Liste des fonctions dans l'établissement de santé pour les listes de formulaire
     */
    public function getFonctionsEtablissementSanteChoices()
    {
        return $this->referenceManager->findByCode('FONCTION_ES');
    }

    /**
     * Retourne la liste des utilisateurs pour les listes de formulaire.
     *
     * @return array Liste des utilisateurs pour les listes de formulaire
     */
    public function getUsersChoices()
    {
        return $this->userManager->findBy(['enabled' => true]);
    }

    /**
     * Retourne la liste des ambassadeurs pour les listes de formulaire.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null $region La région des ambassadeurs
     *
     * @return array Liste des ambassadeurs pour les listes de formulaire
     */
    public function getAmbassadeursChoices($region = null)
    {
        if ($region == null) {
            return $this->userManager->getAmbassadeurs();
        }

        return $this->userManager->getAmbassadeursByRegionAndDomaine($region);
    }

    /**
     * Retourne la liste des référents pour les listes de formulaire.
     *
     * @return array Liste des référents pour les listes de formulaire
     */
    public function getReferentsChoices()
    {
        $referents = [];
        $referents['Administrateur'] = $this->userManager->getAdmins();
        $referents['CMSI'] = $this->userManager->getCMSIs();
        $referents['ES et Enregistrés'] = $this->userManager->getESAndEnregistres();
        asort($referents);

        return $referents;
    }

    /**
     * Retourne la liste jsonifiée des utilisateurs.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     *
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonUsers(array $criteres)
    {
        $users = $this->userManager->findBy($criteres);
        $usersListeFormatee = [];

        foreach ($users as $user) {
            $usersListeFormatee[] = [
                'id'        => $user->getId(),
                'nom'       => $user->getLastname(),
                'firstname' => $user->getFirstname(),
            ];
        }

        return json_encode($usersListeFormatee);
    }

    /**
     * Retourne la liste jsonifiée des référents.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     *
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonReferents(array $criteres)
    {
        $users = $this->userManager->getESAndEnregistres($criteres);
        if ($this->ajouteUtilisateurConnecteCommeDemandeur()) {
            array_unshift($users, $this->utilisateurConnecte);
        }

        $usersListeFormatee = [];
        foreach ($users as $user) {
            $usersListeFormatee[] = [
                'id' => $user->getId(),
                'nom' => $user->getLastname(),
                'firstname' => $user->getFirstname(),
                'appellation' => $user->getAppellation(),
            ];
        }

        return json_encode($usersListeFormatee);
    }

    /**
     * Retourne la liste jsonifiée des ambassadeurs.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     *
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonAmbassadeurs(array $criteres)
    {
        $users = $this->userManager->getAmbassadeurs($criteres);
        $usersListeFormatee = [];

        foreach ($users as $user) {
            $usersListeFormatee[] = [
                'id' => $user->getId(),
                'nom' => $user->getLastname(),
                'firstname' => $user->getFirstname(),
                'appellation' => $user->getAppellation(),
            ];
        }

        return json_encode($usersListeFormatee);
    }

    /**
     * Retourne si dans les listes des référents, on ajoute la personne connectée.
     *
     * @return bool VRAI ssi on ajoute l'utilisateur connecté aux listes de référents
     */
    private function ajouteUtilisateurConnecteCommeDemandeur()
    {
        return $this->utilisateurConnecte->hasRoleCmsi() || $this->aclManager->checkAuthorization(
            $this->router->generate('hopital_numerique_intervention_admin_demande_nouveau'),
            $this->utilisateurConnecte
        );
    }
}

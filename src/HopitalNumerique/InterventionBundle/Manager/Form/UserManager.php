<?php
/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use Symfony\Component\Security\Core\SecurityContext;
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
     * @var \HopitalNumerique\UserBundle\Entity\User L'utilisateur connecté
     */
    private $utilisateurConnecte;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router Router de l'application
     */
    private $router;
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager Le manager de l'entité User
     */
    private $userManager;
    /**
     * @var \Nodevo\AclBundle\Manager\AclManager Le manager de l'entité Acl
     */
    private $aclManager;
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager Manager de Reference
     */
    private $referenceManager;
    /**
     * @var \HopitalNumerique\EtablissementBundle\Manager\EtablissementManager Manager de Etablissement
     */
    private $etablissementManager;

    /**
     * Constructeur du manager gérant les formulaires utilisateurs.
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router Router de l'application
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager Le manager de l'entité User
     * @param \Nodevo\AclBundle\Manager\AclManager $aclManager Le manager de l'entité Acl
     * @param \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager Manager de Reference
     * @param \HopitalNumerique\EtablissementBundle\Manager\EtablissementManager $etablissementManager Manager de Etablissement
     * @return void
     */
    public function __construct(SecurityContext $securityContext, Router $router, \HopitalNumerique\UserBundle\Manager\UserManager $userManager, AclManager $aclManager, ReferenceManager $referenceManager, EtablissementManager $etablissementManager)
    {
        $this->router = $router;
        $this->userManager = $userManager;
        $this->aclManager = $aclManager;
        $this->referenceManager = $referenceManager;
        $this->etablissementManager = $etablissementManager;
        
        $securityContext = $securityContext;
        $this->utilisateurConnecte = $securityContext->getToken()->getUser();
    }

    /**
     * Retourne la liste des civilités pour les listes de formulaire.
     *
     * @return array Liste des civilités pour les listes de formulaire
     */
    public function getCivilitesChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'CIVILITE'));
    }
    /**
     * Retourne la liste des titres pour les listes de formulaire.
     *
     * @return array Liste des titres pour les listes de formulaire
     */
    public function getTitresChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'TITRE'));
    }
    /**
     * Retourne la liste des régions pour les listes de formulaire.
     *
     * @return array Liste des régions pour les listes de formulaire
     */
    public function getRegionsChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'REGION'), array('libelle' => 'ASC'));
    }
    /**
     * Retourne la liste des départements pour les listes de formulaire.
     *
     * @return array Liste des départements pour les listes de formulaire
     */
    public function getDepartementsChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'DEPARTEMENT'), array('libelle' => 'ASC'));
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
        return $this->referenceManager->findBy(array('code' => 'FONCTION_ES'), array('libelle' => 'ASC'));
    }
    /**
     * Retourne la liste des utilisateurs pour les listes de formulaire.
     *
     * @return array Liste des utilisateurs pour les listes de formulaire
     */
    public function getUsersChoices()
    {
        return $this->userManager->findBy(array('enabled' => true));
    }
    /**
     * Retourne la liste des ambassadeurs pour les listes de formulaire.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null $region La région des ambassadeurs
     * @return array Liste des ambassadeurs pour les listes de formulaire
     */
    public function getAmbassadeursChoices($region = null)
    {
        if ($region == null)
            return $this->userManager->getAmbassadeurs();
        return $this->userManager->getAmbassadeursByRegionAndDomaine($region);
    }
    /**
     * Retourne la liste des référents pour les listes de formulaire.
     *
     * @return array Liste des référents pour les listes de formulaire
     */
    public function getReferentsChoices()
    {
        $referents = $this->userManager->getESAndEnregistres();
        if ($this->ajouteUtilisateurConnecteCommeDemandeur())
            $referents[] = $this->utilisateurConnecte;
        
        return $referents;
    }


    /**
     * Retourne la liste jsonifiée des utilisateurs.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonUsers(array $criteres)
    {
        $users = $this->userManager->findBy($criteres);
        $usersListeFormatee = array();

        foreach ($users as $user)
        {
            $usersListeFormatee[] = array('id' => $user->getId(), 'nom' => $user->getNom(), 'prenom' => $user->getPrenom());
        }

        return json_encode($usersListeFormatee);
    }
    /**
     * Retourne la liste jsonifiée des référents.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonReferents(array $criteres)
    {
        $users = $this->userManager->getESAndEnregistres($criteres);
        if ($this->ajouteUtilisateurConnecteCommeDemandeur())
            array_unshift($users, $this->utilisateurConnecte);

        $usersListeFormatee = array();
        foreach ($users as $user)
        {
            $usersListeFormatee[] = array(
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'appellation' => $user->getAppellation()
            );
        }

        return json_encode($usersListeFormatee);
    }
    /**
     * Retourne la liste jsonifiée des ambassadeurs.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonAmbassadeurs(array $criteres)
    {
        $users = $this->userManager->getAmbassadeurs($criteres);
        $usersListeFormatee = array();
    
        foreach ($users as $user)
        {
            $usersListeFormatee[] = array(
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'appellation' => $user->getAppellation()
            );
        }
    
        return json_encode($usersListeFormatee);
    }
    
    /**
     * Retourne si dans les listes des référents, on ajoute la personne connectée.
     * 
     * @return boolean VRAI ssi on ajoute l'utilisateur connecté aux listes de référents
     */
    private function ajouteUtilisateurConnecteCommeDemandeur()
    {
        return ($this->utilisateurConnecte->hasRoleCmsi() || $this->aclManager->checkAuthorization($this->router->generate('hopital_numerique_intervention_admin_demande_nouveau'), $this->utilisateurConnecte));
    }
}

<?php
/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;

/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 */
class UserManager
{
    /**
     * @var \HopitalNumerique\UserBundle\Manager\UserManager Le manager de l'entité User
     */
    private $userManager;
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
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager Le manager de l'entité User
     * @param \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager Manager de Reference
     * @param \HopitalNumerique\EtablissementBundle\Manager\EtablissementManager $etablissementManager Manager de Etablissement
     * @return void
     */
    public function __construct(\HopitalNumerique\UserBundle\Manager\UserManager $userManager, ReferenceManager $referenceManager, EtablissementManager $etablissementManager)
    {
        $this->userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->etablissementManager = $etablissementManager;
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
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $region La région des ambassadeurs
     * @return array Liste des ambassadeurs pour les listes de formulaire
     */
    public function getAmbassadeursChoices(Reference $region)
    {
        return $this->userManager->getAmbassadeursByRegionAndDomaine($region);
        /*return $this->userManager->findBy(array(
            'enabled' => true,
            'region' => $region
        ));*/
    }
    /**
     * Retourne la liste des référents pour les listes de formulaire.
     *
     * @return array Liste des référents pour les listes de formulaire
     */
    public function getReferentsChoices()
    {
        return $this->userManager->getUsersGroupeEtablissement();
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
        $users = $this->userManager->getUsersGroupeEtablissement($criteres);
        $usersListeFormatee = array();

        foreach ($users as $user)
        {
            $usersListeFormatee[] = array('id' => $user->getId(), 'nom' => $user->getNom(), 'prenom' => $user->getPrenom());
        }

        return json_encode($usersListeFormatee);
    }
}

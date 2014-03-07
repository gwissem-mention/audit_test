<?php
/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manager pour le formulaire utilisateur propre aux demandes d'intervention.
 */
class UserManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;
    
    /**
     * Constructeur du manager gérant les formulaires utilisateurs.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Retourne la liste des civilités pour les listes de formulaire.
     *
     * @return array Liste des civilités pour les listes de formulaire
     */
    public function getCivilitesChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'CIVILITE'));
    }
    /**
     * Retourne la liste des titres pour les listes de formulaire.
     *
     * @return array Liste des titres pour les listes de formulaire
     */
    public function getTitresChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'TITRE'));
    }
    /**
     * Retourne la liste des régions pour les listes de formulaire.
     *
     * @return array Liste des régions pour les listes de formulaire
     */
    public function getRegionsChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'REGION'), array('libelle' => 'ASC'));
    }
    /**
     * Retourne la liste des départements pour les listes de formulaire.
     *
     * @return array Liste des départements pour les listes de formulaire
     */
    public function getDepartementsChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'DEPARTEMENT'), array('libelle' => 'ASC'));
    }
    /**
     * Retourne la liste des établissements pour les listes de formulaire.
     *
     * @return array Liste des établissements pour les listes de formulaire
     */
    public function getEtablissementsChoices()
    {
        return $this->container->get('hopitalnumerique_etablissement.manager.etablissement')->findAll();
    }
    /**
     * Retourne la liste des fonctions dans l'établissement de santé pour les listes de formulaire.
     *
     * @return array Liste des fonctions dans l'établissement de santé pour les listes de formulaire
     */
    public function getFonctionsEtablissementSanteChoices()
    {
        return $this->container->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'FONCTION_ES'), array('libelle' => 'ASC'));
    }

    /**
     * Retourne la liste jsonifiée des utilisateurs.
     *
     * @param array $criteres Le filtre à appliquer sur la liste
     * @return string La liste des utilisateurs jsonifiée
     */
    public function jsonUsers(array $criteres)
    {
        $users = $this->container->get('hopitalnumerique_user.manager.user')->findBy($criteres);
        $usersListeFormatee = array();

        foreach ($users as $user)
        {
            $usersListeFormatee[] = array(
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom()
            );
        }

        return json_encode($usersListeFormatee);
    }
}
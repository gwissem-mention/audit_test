<?php
/**
 * Manager pour les objets utilisés dans les formulaires des demandes d'intervention.
 *
 * @author Guillaume Neveux <gneveux@nodevo.com>
 */
namespace HopitalNumerique\ObjetBundle\Manager\Form;

use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager pour les objets utilisés dans les formulaires des demandes d'intervention.
 */
class ObjetManagerForm
{
  /**
   * @var HopitalNumerique\ObjetBundle\Manager\ObjetManager
   */
  private $objetManager;

  /**
   * @var HopitalNumerique\UserBundle\Manager\UserManager
   */
  private $userManager;

  /**
   * Constructeur du manager gérant les formulaires utilisateurs.
   *
   * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
   * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager Le manager de l'entité User
   * @return void
   */
    public function __construct(ObjetManager $objetManager, UserManager $userManager)
    {
        $this->objetManager = $objetManager;
        $this->userManager = $userManager;
    }

    /**
     * Retourne la liste des référents pour les listes de formulaire.
     *
     * @return array Liste des référents pour les listes de formulaire
     */
    public function getConcernesChoices()
    {
        $referents = array();
        $referents['Ambassadeurs'] = $this->userManager->getAmbassadeurs();
        $referents['Experts'] = $this->userManager->getExperts();
        asort($referents);
        return $referents;
    }
}

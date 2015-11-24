<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

class GroupeManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe';

    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     * 
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarres(Domaine $domaine)
    {
        return $this->getRepository()->findNonDemarres($domaine, true);
    }
    
    /**
     * Retourne les groupes en cours.
     * 
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCours(Domaine $domaine)
    {
        return $this->getRepository()->findEnCours($domaine, null, true);
    }

    /**
     * Retourne les groupes en cours.
     * 
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCoursByUser(Domaine $domaine, User $user)
    {
        return $this->getRepository()->findEnCours($domaine, $user, true);
    }

    /**
     * Retourne les données pour le grid.
     *
     * @return array Données
     */
    public function getGridData(\StdClass $filtre)
    {
        return $this->getRepository()->getGridData($filtre->value['domaines']);
    }
}

<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;


/**
 * Inscription.
 *
 * @ORM\Table(name="hn_communautepratique_groupe_user")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository")
 */
class Inscription
{
    /**
     * @var Groupe
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id")
     */
    private $groupe;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")
     */
    private $user;

    /**
     * Attribute Actif.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $actif;

    public function __construct(Groupe $groupe, User $user)
    {
        $this->groupe = $groupe;
        $this->user = $user;
        $this->actif = false;
    }

    /**
     * Get groupe.
     *
     * @return Groupe
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set groupe.
     *
     * @return Inscription
     */
    public function setGroupe(Groupe $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @return Inscription
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get actif.
     *
     * @return bool
     */
    public function isActif()
    {
        return $this->actif;
    }

    /**
     * Set actif.
     *
     * @return Inscription
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }
}

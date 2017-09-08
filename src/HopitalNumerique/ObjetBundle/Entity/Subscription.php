<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Subscription to publication (object) or publication part (infradoc).
 *
 * @ORM\Table(name="hn_objet_subscription")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @var int
     *
     * @ORM\Column(name="comm_id", type="integer", options={"comment"="Subscription id"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="listeCommentaires")
     * @ORM\JoinColumn(referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;

    /**
     * @var Contenu
     *
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="listeCommentaires")
     * @ORM\JoinColumn(referencedColumnName="con_id", onDelete="CASCADE", nullable=true)
     */
    protected $contenu;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"comment"="Subscription creation date time"})
     */
    protected $creationDate;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get object.
     *
     * @return Objet $objet
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set object.
     *
     * @param Objet $objet
     *
     * @return Subscription
     */
    public function setObjet(Objet $objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Set infradoc.
     *
     * @return Contenu $contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Get infradoc.
     *
     * @param Contenu $contenu
     */
    public function setContenu(Contenu $contenu)
    {
        $this->contenu = $contenu;
    }

    /**
     * Set user.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     *
     * @return Subscription
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

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
}

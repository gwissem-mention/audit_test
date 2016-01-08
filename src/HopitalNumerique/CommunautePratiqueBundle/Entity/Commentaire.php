<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Entité Fiche.
 *
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\CommentaireRepository")
 * @ORM\Table(name="hn_communautepratique_commentaire")
 */
class Commentaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="com_id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(
     *     targetEntity="HopitalNumerique\UserBundle\Entity\User",
     *     inversedBy="communautePratiqueCommentaires"
     * )
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="com_message", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="com_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="com_date_derniere_modification", type="datetime", nullable=true)
     */
    private $dateDerniereModification;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
     *
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="commentaires")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", nullable=true, onDelete="CASCADE")
     */
    private $groupe;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche
     *
     * @ORM\ManyToOne(targetEntity="Fiche", inversedBy="commentaires")
     * @ORM\JoinColumn(name="fic_id", referencedColumnName="fic_id", nullable=true, onDelete="CASCADE")
     */
    private $fiche;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Commentaire
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Commentaire
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateDerniereModification
     *
     * @param \DateTime $dateDerniereModification
     * @return Commentaire
     */
    public function setDateDerniereModification($dateDerniereModification)
    {
        $this->dateDerniereModification = $dateDerniereModification;

        return $this;
    }

    /**
     * Get dateDerniereModification
     *
     * @return \DateTime
     */
    public function getDateDerniereModification()
    {
        return $this->dateDerniereModification;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Commentaire
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set groupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe
     * @return Commentaire
     */
    public function setGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set fiche
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche
     * @return Commentaire
     */
    public function setFiche(\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiche)
    {
        $this->fiche = $fiche;

        return $this;
    }

    /**
     * Get fiche
     *
     * @return \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche
     */
    public function getFiche()
    {
        return $this->fiche;
    }
}

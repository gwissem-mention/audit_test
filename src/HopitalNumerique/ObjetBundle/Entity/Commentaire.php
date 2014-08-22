<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commentaire
 *
 * @ORM\Table(name="hn_objet_commentaire")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\CommentaireRepository")
 */
class Commentaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="comm_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="comm_dateCreation", type="datetime")
     */
    protected $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="comm_texte", type="text")
     */
    protected $texte;

    /**
     * @var boolean
     *
     * @ORM\Column(name="comm_publier", type="boolean")
     */
    protected $publier;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="listeCommentaires")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="listeCommentaires")
     * @ORM\JoinColumn(name="con_id", referencedColumnName="con_id", onDelete="CASCADE", nullable=true)
     */
    protected $contenu;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_user", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    protected $user;


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
     * Get dateCreation
     *
     * @return String 
     */
    public function getDateCreationString()
    {
        return $this->dateCreation->format('d/m/Y');
    }

    /**
     * Set texte
     *
     * @param string $texte
     * @return Commentaire
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string 
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set publier
     *
     * @param boolean $publier
     * @return Commentaire
     */
    public function setPublier($publier)
    {
        $this->publier = $publier;

        return $this;
    }

    /**
     * Get publier
     *
     * @return boolean 
     */
    public function getPublier()
    {
        return $this->publier;
    }
    
    /**
     * Get objet
     *
     * @return Objet $objet
     */
    public function getObjet()
    {
        return $this->objet;
    }
    
    /**
     * Set objet
     *
     * @param Objet $objet
     */
    public function setObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objet)
    {
        $this->objet = $objet;
    }
    
    /**
     * Get contenu
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }
    
    /**
     * Set contenu
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function setContenu(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenu)
    {
        $this->contenu = $contenu;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Reponse
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
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
}

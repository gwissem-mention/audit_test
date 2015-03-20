<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note
 *
 * @ORM\Table(name="hn_objet_note")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\NoteRepository")
 */
class Note
{
    /**
     * @var integer
     *
     * @ORM\Column(name="note_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="note_dateNote", type="datetime")
     */
    protected $dateNote;

    /**
     * @var integer
     *
     * @ORM\Column(name="note_note", type="integer")
     */
    protected $note;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="listeNotes")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="listeNotes")
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
     * Set dateNote
     *
     * @param \DateTime $dateNote
     * @return Note
     */
    public function setDateNote($dateNote)
    {
        $this->dateNote = $dateNote;

        return $this;
    }

    /**
     * Get dateNote
     *
     * @return \DateTime 
     */
    public function getDateNote()
    {
        return $this->dateNote;
    }

    /**
     * Set note
     *
     * @param integer $note
     * @return Note
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return integer 
     */
    public function getNote()
    {
        return $this->note;
    }



    /**
     * Get note
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet 
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

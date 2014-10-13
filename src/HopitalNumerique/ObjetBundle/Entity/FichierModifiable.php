<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FichierModifiable
 *
 * @ORM\Table(name="hn_objet_fichiermodifiable")
 * @ORM\Entity()
 */
class FichierModifiable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ofm_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_referentAnap", type="string", length=255)
     */
    protected $referentAnap;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_referentAnap", type="string", length=255)
     */
    protected $sourceDocument;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_commentaires", type="text")
     */
    protected $commentaires;

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
     * Set referentAnap
     *
     * @param string $referentAnap
     * @return FichierModifiable
     */
    public function setReferentAnap($referentAnap)
    {
        $this->referentAnap = $referentAnap;

        return $this;
    }

    /**
     * Get referentAnap
     *
     * @return string 
     */
    public function getReferentAnap()
    {
        return $this->referentAnap;
    }

    /**
     * Set sourceDocument
     *
     * @param string $sourceDocument
     * @return FichierModifiable
     */
    public function setSourceDocument($sourceDocument)
    {
        $this->sourceDocument = $sourceDocument;

        return $this;
    }

    /**
     * Get sourceDocument
     *
     * @return string 
     */
    public function getSourceDocument()
    {
        return $this->sourceDocument;
    }

    /**
     * Set commentaires
     *
     * @param string $commentaires
     * @return FichierModifiable
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires
     *
     * @return string 
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }
}

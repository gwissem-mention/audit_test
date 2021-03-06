<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ToolsBundle\Tools\Systeme;
use Nodevo\ToolsBundle\Traits\ImageTrait;

/**
 * ExpBesoinReponses.
 *
 * @ORM\Table(name="hn_recherche_expbesoin_reponse")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\ExpBesoinReponsesRepository")
 */
class ExpBesoinReponses
{
    use ImageTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="expbr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="expb_order", type="smallint", options = {"comment" = "Ordre de la question"})
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="expbr_libelle", type="string", length=255)
     */
    protected $libelle;

    /**
     * @var ExpBesoin
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoin", inversedBy="reponses")
     * @ORM\JoinColumn(name="expb_id", referencedColumnName="expb_id", nullable=true, onDelete="CASCADE")
     */
    protected $question;

    /**
     * @var bool
     *
     * @ORM\Column(name="expbr_autreQuestion", type="boolean", options = {"comment" = " ?"})
     */
    protected $autreQuestion;

    /**
     * @var string
     *
     * @ORM\Column(name="expbr_image", type="text", nullable=true, length=255)
     */
    protected $image;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $imageFile;

    /**
     * @var ExpBesoin
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoin")
     * @ORM\JoinColumn(name="expb_id_redirection", referencedColumnName="expb_id", nullable=true, onDelete="CASCADE")
     */
    protected $redirigeQuestion;

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
     * Get order.
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     *
     * @return ExpBesoinReponses
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return ExpBesoinReponses
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Get question.
     *
     * @return ExpBesoin
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question.
     *
     * @param $expBesoin
     *
     * @internal param ExpBesoin $question
     */
    public function setQuestion($expBesoin)
    {
        if ($expBesoin instanceof ExpBesoin) {
            $this->question = $expBesoin;
        } else {
            $this->question = null;
        }
    }

    /**
     * Set autreQuestion.
     *
     * @param bool $autreQuestion
     *
     * @return ExpBesoinReponses
     */
    public function setAutreQuestion($autreQuestion)
    {
        $this->autreQuestion = $autreQuestion;

        return $this;
    }

    /**
     * Get autreQuestion.
     *
     * @return bool
     */
    public function isAutreQuestion()
    {
        return $this->autreQuestion;
    }

    /**
     * Get redirigeQuestion.
     *
     * @return ExpBesoin $question
     */
    public function getRedirigeQuestion()
    {
        return $this->redirigeQuestion;
    }

    /**
     * Set redirigeQuestion.
     *
     * @param ExpBesoin $expBesoin
     */
    public function setRedirigeQuestion(ExpBesoin $expBesoin)
    {
        $this->redirigeQuestion = $expBesoin;
    }

    /**
     * @return string
     */
    public function getImageUploadDir()
    {
        return 'media' . DIRECTORY_SEPARATOR . 'expression-besoin-reponse';
    }

    /**
     * @return bool
     */
    public function imageFileIsValid()
    {
        return null !== $this->imageFile && $this->imageFile->getClientSize() <= Systeme::getFileUploadMaxSize();
    }

    /**
     * Retourne l'URL de l'image.
     *
     * @return string|null URL
     */
    public function getImageUrl()
    {
        if (null !== $this->image) {
            return '/' . str_replace(DIRECTORY_SEPARATOR, '/', $this->getImageUploadDir()) . '/' . $this->image;
        }

        return null;
    }
}

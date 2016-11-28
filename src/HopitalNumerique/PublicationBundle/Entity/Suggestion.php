<?php

namespace HopitalNumerique\PublicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Suggestion
 *
 * @ORM\Table(name="hn_suggestion")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PublicationBundle\Repository\SuggestionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Suggestion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 255,
     * )
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $synthesis;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Expression(
     *     "null !== this.getLink() || null !== this.getPath() || null !== this.getFile()",
     *     message="Veuillez renseigner un lien ou un fichier joint",
     *     groups={"front_add"}
     * )
     */
    private $link;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="state", referencedColumnName="ref_id")
     */
    private $state;

    /**
     * @var Domaine[]
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="hn_suggestion_domaine",
     *     joinColumns={@ORM\JoinColumn(name="sug_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    private $domains;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var UploadedFile
     *
     * @Assert\File(
     *     maxSize = "10M"
     * )
     * @Assert\Expression(
     *     "null !== this.getFile() || null !== this.getLink()",
     *     message="Veuillez renseigner un lien ou un fichier joint",
     *     groups={"front_add"}
     * )
     */
    private $file;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="usr_id")
     */
    private $user;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->domains = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    /**
     * @param string $synthesis
     * @return $this
     */
    public function setSynthesis($synthesis)
    {
        $this->synthesis = $synthesis;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Reference
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param Reference $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return ArrayCollection \HopitalNumerique\DomaineBundle\Entity\Domaine
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param Domaine $domaine
     *
     * @return $this
     */
    public function addDomain(Domaine $domaine)
    {
        $this->domains[] = $domaine;

        return $this;
    }

    /**
     * @param UploadedFile $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        $this->updatedAt = new \DateTime('now');

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $path
     *
     * @return Suggestion
     */
    public function setPath($path)
    {
        if (is_null($path) && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }

        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        $result = null;

        if (!is_null($this->path)) {
            $result = $this->path;
        }

        if (is_null($result)) {
            return null;
        }

        return $this->getUploadRootDir() . '/' . $result;
    }

    /**
     * @return null|string
     */
    public function getWebPath()
    {
        $result = null;

        if (!is_null($this->path)) {
            $result = $this->path;
        }

        if (is_null($result)) {
            return null;
        }

        return $this->getUploadDir() . '/' . $result;
    }

    /**
     * @return string
     */
    public function getTypeMime()
    {
        $result = $this->path;

        if (!$result || is_null($result)) {
            return "";
        }

        return substr($result, strrpos($result, ".") + 1);
    }

    /**
     * @return string
     */
    public function getUploadRootDir()
    {
        return __WEB_DIRECTORY__ . '/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'medias/Suggestions/Fichiers';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            if (file_exists($this->getAbsolutePath())) {
                unlink($this->getAbsolutePath());
            }

            $this->path = time() . $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        if (null !== $this->file) {
            $this->file->move($this->getUploadRootDir(), $this->path);
            unset($this->file);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($this->getAbsolutePath() && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());

            $this->path = null;
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}

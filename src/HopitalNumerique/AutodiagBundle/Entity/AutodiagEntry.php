<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Autodiag entry - a response to an autodiag
 *
 * @ORM\Table(name="ad_entry")
 * @ORM\Entity
 */
class AutodiagEntry
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
     * @var Synthesis
     *
     * @ORM\ManyToOne(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis",
     *     inversedBy="entries",
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $synthesis;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value", mappedBy="entry")
     */
    private $values;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    public function __construct(Synthesis $synthesis, User $user = null)
    {
        $this->synthesis = $synthesis;
        $this->user = $user;
        $this->values = new ArrayCollection();

        $synthesis->addEntry($this);
    }

    public static function create(Autodiag $autodiag, User $user = null)
    {
        $entry = new self(Synthesis::create($autodiag), $user);

        return $entry;
    }

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
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addValue($value)
    {
        $this->values->add($value);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}

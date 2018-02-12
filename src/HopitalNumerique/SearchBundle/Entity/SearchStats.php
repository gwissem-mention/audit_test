<?php

namespace HopitalNumerique\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class SearchStats
 *
 * @ORM\Entity
 * @ORM\Table(name="hn_search_stats")
 */
class SearchStats
{
    /**
     * @var int
     *
     * @ORM\Column(name="search_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="search_token", type="string", length=255)
     */
    protected $token;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="search_date", type="datetime")
     */
    protected $date;

    /**
     * @var int
     *
     * @ORM\Column(name="search_results", type="smallint")
     */
    protected $nbResults;

    /**
     * @var string
     *
     * @ORM\Column(name="search_index", type="string", length=255)
     */
    protected $index;

    /**
     * @var string
     *
     * @ORM\Column(name="search_term", type="string", length=255)
     */
    protected $term;

    /**
     * @var int
     *
     * @ORM\Column(name="search_size", type="integer")
     */
    protected $size;

    /**
     * @var int
     *
     * @ORM\Column(name="search_from", type="integer")
     */
    protected $from;

    /**
     * @var string
     *
     * @ORM\Column(name="search_source", type="string", length=255)
     */
    protected $source;

    /**
     * @var bool
     *
     * @ORM\Column(name="search_is_production", type="boolean")
     */
    protected $isProduction;

    /**
     * SearchStats constructor.
     *
     * @param string $token
     * @param \DateTime $date
     * @param int $nbResults
     * @param string $index
     * @param string $term
     * @param int $size
     * @param int $from
     * @param bool $isProduction
     */
    public function __construct($token, \DateTime $date, $nbResults, $index, $term, $size, $from, $isProduction)
    {
        $this->token = $token;
        $this->date = $date;
        $this->nbResults = $nbResults;
        $this->index = $index;
        $this->term = $term;
        $this->size = $size;
        $this->from = $from;
        $this->isProduction = $isProduction;
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return SearchStats
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function isProduction()
    {
        return $this->isProduction;
    }
}

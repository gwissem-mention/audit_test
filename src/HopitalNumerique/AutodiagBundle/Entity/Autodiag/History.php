<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Autodiag history
 *
 * @ORM\Table(name="ad_autodiag_history")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\Autodiag\HistoryRepository")
 */
class History
{
    const HISTORY_ENTRY_SURVEY = 'survey';
    const HISTORY_ENTRY_ALGORITHM = 'algorithm';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Model
     *
     * @var Autodiag
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $username;

    /**
     * History entry type
     *
     * @var integer
     *
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * History date and time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * History constructor.
     * @param Autodiag $autodiag
     * @param User $user
     */
    private function __construct(Autodiag $autodiag, User $user)
    {
        $this->autodiag = $autodiag;
        $this->username = $user->getPrenom() . ' ' . $user->getNom();
        $this->dateTime = new \DateTime();
    }

    public static function createSurveyImport(Autodiag $autodiag, User $user)
    {
        $history = new self($autodiag, $user);
        $history->setType(self::HISTORY_ENTRY_SURVEY);
        return $history;
    }

    public static function createAlgorithmImport(Autodiag $autodiag, User $user)
    {
        $history = new self($autodiag, $user);
        $history->setType(self::HISTORY_ENTRY_ALGORITHM);
        return $history;
    }

    /**
     * Get model
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get entry date and time
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Get history type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set history type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public static function getTypeList()
    {
        return [
            'history.type.survey' => self::HISTORY_ENTRY_SURVEY,
            'history.type.algorithm' => self::HISTORY_ENTRY_ALGORITHM,
        ];
    }
}
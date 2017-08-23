<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\HopitalNumeriqueAutodiagBundle;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Autodiag history.
 *
 * @ORM\Table(name="ad_autodiag_history")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\Autodiag\HistoryRepository")
 */
class History
{
    const HISTORY_ENTRY_SURVEY = 'survey';
    const HISTORY_ENTRY_ALGORITHM = 'algorithm';
    const HISTORY_ENTRY_RESTITUTION = 'resitution';
    const HISTORY_ENTRY_GENERAL = 'general';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Model.
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
     * History entry type.
     *
     * @var int
     *
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * History date and time.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $notify;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $updateReason;

    /**
     * History constructor.
     *
     * @param Autodiag $autodiag
     * @param User     $user
     */
    private function __construct(Autodiag $autodiag, User $user, $notify, $updateReason = '')
    {
        $this->autodiag = $autodiag;
        $this->username = $user->getFirstname() . ' ' . $user->getLastname();
        $this->dateTime = new \DateTime();
        $this->notify = $notify;
        $this->updateReason = $updateReason;
    }

    public static function createSurveyImport(Autodiag $autodiag, User $user, $notify, $updateReason = '')
    {
        $history = new self($autodiag, $user, $notify, $updateReason);
        $history->setType(self::HISTORY_ENTRY_SURVEY);

        return $history;
    }

    public static function createAlgorithmImport(Autodiag $autodiag, User $user, $notify, $updateReason = '')
    {
        $history = new self($autodiag, $user, $notify, $updateReason);
        $history->setType(self::HISTORY_ENTRY_ALGORITHM);

        return $history;
    }

    public static function createRestitutionImport(Autodiag $autodiag, User $user, $notify, $updateReason = '')
    {
        $history = new self($autodiag, $user, $notify, $updateReason);
        $history->setType(self::HISTORY_ENTRY_RESTITUTION);

        return $history;
    }

    public static function createGeneralNotification(Autodiag $autodiag, User $user, $notify, $updateReason = '')
    {
        $history = new self($autodiag, $user, $notify, $updateReason);
        $history->setType(self::HISTORY_ENTRY_GENERAL);

        return $history;
    }



    /**
     * Get model.
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get entry date and time.
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Get history type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set history type.
     *
     * @param int $type
     *
     * @return History
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * @param $notify
     *
     * @return History
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * @param $updateReason
     *
     * @return History
     */
    public function setReason($updateReason)
    {
        $this->updateReason = $updateReason;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->updateReason;
    }

    public static function getTypeList()
    {
        return [
            'history.type.survey' => self::HISTORY_ENTRY_SURVEY,
            'history.type.algorithm' => self::HISTORY_ENTRY_ALGORITHM,
            'history.type.restitution' => self::HISTORY_ENTRY_RESTITUTION,
        ];
    }
}

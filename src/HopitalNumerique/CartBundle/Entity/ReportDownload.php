<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class ReportDownload
 *
 * @ORM\Table(name="hn_cart_report_download")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportDownloadRepository")
 */
class ReportDownload
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Report", inversedBy="downloads")
     */
    protected $report;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $downloadDate;

    /**
     * ReportDownload constructor.
     *
     * @param Report $report
     * @param User   $user
     */
    public function __construct(Report $report, User $user)
    {
        $this->report = $report;
        $this->user = $user;
        $this->downloadDate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     *
     * @return ReportDownload
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
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
     *
     * @return ReportDownload
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDownloadDate()
    {
        return $this->downloadDate;
    }

    /**
     * @param \DateTime $downloadDate
     *
     * @return ReportDownload
     */
    public function setDownloadDate($downloadDate)
    {
        $this->downloadDate = $downloadDate;

        return $this;
    }

    /**
    * @return ReportDownload
    */
    public function updateDownloadDate()
    {
        return $this->setDownloadDate(new \DateTime());
    }
}

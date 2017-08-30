<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Table(name="hn_cart_report_sharing")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CartBundle\Repository\ReportSharingRepository")
 */
class ReportSharing
{
    const TYPE_SHARE = 'share';
    const TYPE_COPY = 'copy';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $target;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $sharedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $sharedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Report", inversedBy="shares")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $report;

    /**
     * @var Report
     *
     * @ORM\OneToOne(targetEntity="HopitalNumerique\CartBundle\Entity\Report", mappedBy="sharedBy")
     */
    protected $copiedReport;

    /**
     * ReportSharing constructor.
     *
     * @param User $sharedBy
     * @param User $target
     * @param Report $report
     * @param string $type
     */
    public function __construct(User $sharedBy, User $target, Report $report, $type)
    {
        $this->sharedBy = $sharedBy;
        $this->target = $target;
        $this->report = $report;
        $this->type = $type;
        $this->sharedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return \DateTime
     */
    public function getSharedAt()
    {
        return $this->sharedAt;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return User
     */
    public function getSharedBy()
    {
        return $this->sharedBy;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Report
     */
    public function getCopiedReport()
    {
        return $this->copiedReport;
    }
}

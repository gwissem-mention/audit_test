<?php

namespace HopitalNumerique\AutodiagBundle\Model;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AutodiagFileImport
 */
class AutodiagFileImport
{
    /**
     * @var Autodiag
     */
    protected $autodiag;

    /**
     * @var UploadedFile
     * @Assert\NotBlank()
     */
    protected $file;

    /**
     * @var bool
     */
    protected $notifyUpdate;

    /**
     * @var string
     */
    protected $updateReason;

    /**
     * AutodiagFileImport constructor.
     *
     * @param Autodiag $autodiag
     */
    public function __construct(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     *
     * @return AutodiagFileImport
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNotifyUpdate()
    {
        return $this->notifyUpdate;
    }

    /**
     * @param mixed $notifyUpdate
     *
     * @return AutodiagFileImport
     */
    public function setNotifyUpdate($notifyUpdate)
    {
        $this->notifyUpdate = $notifyUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateReason()
    {
        return $this->updateReason;
    }

    /**
     * @param string $updateReason
     *
     * @return AutodiagFileImport
     */
    public function setUpdateReason($updateReason)
    {
        $this->updateReason = $updateReason;

        return $this;
    }
}

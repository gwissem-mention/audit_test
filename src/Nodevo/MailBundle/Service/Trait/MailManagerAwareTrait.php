<?php

namespace Nodevo\MailBundle\Service;

use Nodevo\MailBundle\Manager\MailManager;

trait MailManagerAwareTrait
{
    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * @param MailManager|null $mailManager
     */
    public function setMailManager(MailManager $mailManager = null)
    {
        $this->mailManager = $mailManager;
    }
}

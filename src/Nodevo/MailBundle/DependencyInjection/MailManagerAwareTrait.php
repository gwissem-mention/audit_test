<?php

namespace Nodevo\MailBundle\DependencyInjection;

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

<?php

namespace Nodevo\MailBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NodevoMailEvents extends Bundle
{
    /**
     * Dispatch when a recommendation mail is sended
     */
    const RECOMMENDATION_SENDED = 'recommendation_sended';
}

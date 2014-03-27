<?php

namespace HopitalNumerique\ContactBundle\Manager;

use Nodevo\ContactBundle\Manager\ContactManager as NodevoContactManager;

/**
 * Manager de l'entité Contractualisation.
 */
class ContactManager extends NodevoContactManager
{
    protected $_class = 'HopitalNumerique\ContactBundle\Entity\Contact';
}
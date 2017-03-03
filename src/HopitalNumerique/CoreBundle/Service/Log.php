<?php

namespace HopitalNumerique\CoreBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;

class Log
{
    /** @var array */
    private $logs;

    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function Logger($action, $object, $title, $class, $user)
    {
        $value = 'name';
        if ($class == 'HopitalNumerique\UserBundle\Entity\User') {
            $value = 'roles';
        }

        $log = new LogEntry();
        $log->setAction($action);
        $log->setLoggedAt(\DateTime::createFromFormat('d-m-Y H:i:s', date('d-m-Y H:i:s')));
        $log->setObjectId($object->getId());
        $log->setObjectClass($class);
        $log->setVersion(1);
        $log->setData([$value => $title]);
        $log->setUsername($user->getUsername());

        $this->logs[] = $log;

        return $log;
    }

    public function persistLogs()
    {
        if (!empty($this->logs)) {
            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();

            foreach ($this->logs as $log) {
                $em->persist($log);
            }

            $em->flush();
        }
    }
}

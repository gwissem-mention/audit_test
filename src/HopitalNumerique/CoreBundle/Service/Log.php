<?php
namespace HopitalNumerique\CoreBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Gedmo\Loggable\Entity\LogEntry;


class Log
{

    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    public function Logger($action, $object, $title, $class, $user)
    {
        $em = $this->doctrine->getManager();

        $log = new LogEntry();
        $log->setAction($action);
        $log->setLoggedAt(\DateTime::createFromFormat('d-m-Y H:i:s', date('d-m-Y H:i:s')));
        $log->setObjectId($object->getId());
        $log->setObjectClass($class);
        $log->setVersion(1);
        $log->setData(array('name' => $title));
        $log->setUsername($user->getUsername());
        $em->persist($log);
        $em->flush();

        return $log;
    }
}

<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Nodevo\MailBundle\Manager\MailManager;

class UserUpdateListener implements EventSubscriberInterface
{
    protected $_managerMail;
    protected $_mailer;
    protected $_userDomainesId;

    function __construct(MailManager $managerMail, $mailer)
    {
        $this->_managerMail = $managerMail;
        $this->_mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'user_nodevo.update'        => 'checkDomaineOnUpdate',
            'user_nodevo.before_update' => 'beforeUserUpdate'
        ];
    }

    public function beforeUserUpdate(UserEvent $event)
    {
        $this->_userDomainesId = $event->getDomainesId();
    }

    public function checkDomaineOnUpdate(UserEvent $event)
    {
        //Récupération de l'utilisateur avant modif
        $userDomainesIdBeforeUpdate = $this->_userDomainesId;
        //Récupération de l'utilisateur après modif
        $userDomainesIdAfterUpdate = $event->getUser()->getDomainesId();

        //Envoie d'un mail si le domaine a changé lors de l'update
        if((count($userDomainesIdBeforeUpdate) != 0 || count($userDomainesIdAfterUpdate) != 0) && ($userDomainesIdBeforeUpdate != $userDomainesIdAfterUpdate))
        {
            $user = $event->getUser();
            $domainesString = '<ul>';

            foreach ($user->getDomaines() as $domaine) 
            {
                $domainesString .= '<li><a href="'.$domaine->getUrl().'">'.$domaine->getUrl().'</a></li>';
            }

            $domainesString .= '</ul>';

            $mail = $this->_managerMail->sendDomaineChanged($user, array('domaines' => $domainesString));
            $this->_mailer->send($mail);
        }
    }
}
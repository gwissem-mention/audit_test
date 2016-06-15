<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\ReferenceBundle\Doctrine\ReferencementDeleter;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Nodevo\MailBundle\Manager\MailManager;

class UserUpdateListener implements EventSubscriberInterface
{
    protected $_managerMail;
    protected $_mailer;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Doctrine\ReferencementDeleter ReferencementDeleter
     */
    private $referencementDeleter;

    protected $_userDomainesId;


    function __construct(MailManager $managerMail, $mailer, ReferencementDeleter $referencementDeleter)
    {
        $this->_managerMail = $managerMail;
        $this->_mailer = $mailer;
        $this->referencementDeleter = $referencementDeleter;
    }

    public static function getSubscribedEvents()
    {
        return [
            'user_nodevo.update'        => 'update',
            'user_nodevo.before_update' => 'beforeUserUpdate'
        ];
    }

    public function beforeUserUpdate(UserEvent $event)
    {
        $this->_userDomainesId = $event->getDomainesId();
    }

    /**
     * L'utilisateur est màj.
     *
     * @param \HopitalNumerique\UserBundle\Event\UserEvent $event Event
     */
    public function update(UserEvent $event)
    {
        $this->checkDomaineOnUpdate($event);
        $this->removeAmbassadeurReferences($event->getUser());
    }

    private function checkDomaineOnUpdate(UserEvent $event)
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
                $domainesString .= '<li><a href="'.$domaine->getUrl().'">'.$domaine->getNom().'</a></li>';
            }

            $domainesString .= '</ul>';

            $mail = $this->_managerMail->sendDomaineChanged($user, array('domaines' => $domainesString));
            $this->_mailer->send($mail);
        }
    }

    /**
     * Supprime les éventuelles références de l'ambassadeur si l'utilisateur ne l'est plus.
     */
    private function removeAmbassadeurReferences(User $user)
    {
        $this->referencementDeleter->removeAmbassadeurReferences($user);
    }
}

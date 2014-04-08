<?php

namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use FOS\UserBundle\Controller\ResettingController as BaseController;


/**
 * Controller managing the resetting of the password
 *
 * @author Gaetan Melchilsen <gmelchilsen@nodevo.com>
 */
class ResettingController extends BaseController
{
    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:request.html.'.$this->getEngine(), array('invalid_username' => $username));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine());
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }
        
        //Création de l'url de reset de mot de passe
        $options = array(
                'url' => $this->container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true),
                'nom' => $user->getUsername()
        );
        $mailReset = $this->container->get('nodevo_mail.manager.mail')->sendResetPasswordMail($user, $options);
        $this->container->get('mailer')->send($mailReset);
        
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email',
            array('email' => $this->getObfuscatedEmail($user))
        ));
    }
}

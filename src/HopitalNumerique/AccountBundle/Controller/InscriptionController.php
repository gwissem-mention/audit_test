<?php
namespace HopitalNumerique\AccountBundle\Controller;

use HopitalNumerique\AccountBundle\Form\Type\InscriptionType;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Tools\Password;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InscriptionController extends Controller
{
    /**
     * Popin simple d'inscription.
     * @param Request $request
     * @param string $urlRedirection
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function popinAction(Request $request, $urlRedirection = '')
    {
        if (null !== $this->getUser()) {
            return new Response('');
        }

        $user = $this->container->get('hopitalnumerique_user.manager.user')->createEmpty();
        $this->completeUser($request, $user);

        $inscriptionForm = $this->createForm(InscriptionType::class, $user, [
            'url_redirection' => $urlRedirection,
        ]);
        $inscriptionForm->handleRequest($request);

        if ($inscriptionForm->isSubmitted()) {
            if ($inscriptionForm->isValid()) {
                // Envoi du courriel de confirmation
                $user->setEnabled(false);
                $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
                $this->get('mailer')->send($this->get('nodevo_mail.manager.mail')->sendAjoutUserMail($user, []));

                $this->container->get('hopitalnumerique_user.manager.user')->save($user);

                if ($this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->isWantToSaveRequete()) {
                    $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setAnonymousUser(true);
                }

                // Connexion automatique
                $token = new UsernamePasswordToken($user, null, 'frontoffice_connecte', $user->getRoles());
                $this->container->get('security.context')->setToken($token);
                $this->container->get('event_dispatcher')->dispatch('security.interactive_login', new InteractiveLoginEvent($request, $token));

                $this->addFlash('success', 'Inscription validée.');
            } else {
                if (count($inscriptionForm->getErrors()) > 0) {
                    foreach ($inscriptionForm->getErrors() as $formError) {
                        $this->addFlash('danger', $formError->getMessage());
                    }
                } else {
                    $this->addFlash('danger', 'Inscription non validée.');
                }
            }

            if ($this->container->get('session')->has('urlToRedirect')) {
                return $this->redirect($this->container->get('session')->get('urlToRedirect'));
            } else {
                return $this->redirect($inscriptionForm->get('urlRedirection')->getData());
            }
        }

        return $this->render('HopitalNumeriqueAccountBundle:Inscription:popin.html.twig', [
            'inscriptionForm' => $inscriptionForm->createView(),
        ]);
    }

    /**
     * Complète l'utilisateur nouveau avec des valeurs obligatoires par défaut.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param \HopitalNumerique\UserBundle\Entity\User $user User
     */
    private function completeUser(Request $request, User &$user)
    {
        $passwordTool = new Password();

        $user->setDateInscription(new \DateTime());
        $user->setDateLastUpdate(new \DateTime());
        $user->setPlainPassword(str_shuffle($passwordTool->generate(3, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') . $passwordTool->generate(3, 'abcdefghijklmnopqrstuvwyyz') . $passwordTool->generate(2, '1234567890')));
        $user->setCivilite($this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::CIVILITE_MONSIEUR_ID));
        $user->setNom(' ');
        $user->setPrenom(' ');
        $user->setEtat($this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::STATUT_ACTIF_ID));
        $user->addDomaine($this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());
        $user->setRoles([$this->container->get('nodevo_role.manager.role')->findOneBy(['role' => 'ROLE_ENREGISTRE_9'])->getRole()]);

        if ($request->request->has('inscription')) {
            $inscriptionFormPost = $request->request->get('inscription');
            $user->setUsername($inscriptionFormPost['email']);
        }
    }
}

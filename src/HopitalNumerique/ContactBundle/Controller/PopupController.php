<?php
namespace HopitalNumerique\ContactBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Popup de contact.
 */
class PopupController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affichage de la popup.
     */
    public function indexAction(Request $request)
    {
        if (!$request->request->has('destinataires') || !$request->request->has('urlRedirection')) {
            throw new \Exception('Les destinataires et l\'URL de redirection doivent être précisés.');
        }

        $contactForm = $this->createForm('hopitalnumerique_contactbundle_popup', null, array(
            'destinataires' => $request->request->get('destinataires'),
            'urlRedirection' => $request->request->get('urlRedirection')
        ));
        if ($request->request->has('objet')) {
            $contactForm->get('objet')->setData($request->request->get('objet'));
        }

        return $this->render(
            'HopitalNumeriqueContactBundle:Popup:index.html.twig',
            array(
                'contactForm' => $contactForm->createView(),
                'destinataires' => $request->request->get('destinataires')
            )
        );
    }

    /**
     * Affichage de la popup.
     */
    public function inviteAction(Request $request)
    {
        if (!$request->request->has('urlRedirection')) {
            throw new \Exception('L\'URL de redirection doivent être précisés.');
        }

        $inviteForm = $this->createForm('hopitalnumerique_contactbundle_popup_invite', null, array(
            'urlRedirection' => $request->request->get('urlRedirection')
        ));

        return $this->render(
            'HopitalNumeriqueContactBundle:Popup:invite.html.twig',
            array(
                'inviteForm' => $inviteForm->createView(),
            )
        );
    }

    /**
     * Lorsque le formulaire de la popup de d'invitation est soumis.
     */
    public function submitInviteAction(Request $request)
    {
        $form = $request->request->get('hopitalnumerique_contactbundle_popup_invite');

        if (!empty($form['destinataires'])) {
            $destinataires = explode(",", $form['destinataires']);
            $this->get('nodevo_mail.manager.mail')->sendInvitationMail($this->get('security.context')->getToken()->getUser(), $destinataires);
            $this->get('session')->getFlashBag()->add('success', 'Votre invitation a été envoyé.');
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'Invitation non envoyé.');
        }

        return $this->redirect($form['urlRedirection']);
    }

    /**
     * Lorsque le formulaire de la popup de contact est soumis.
     */
    public function submitAction(Request $request)
    {
        $contactForm = $this->createForm('hopitalnumerique_contactbundle_popup');
        $contactForm->handleRequest($request);

        if ($contactForm->isValid()) {
            $this->get('session')->getFlashBag()->add('success', 'Votre message a été envoyé.');
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'Message non envoyé.');
        }

        return $this->redirect($contactForm->get('urlRedirection')->getData());
    }
}

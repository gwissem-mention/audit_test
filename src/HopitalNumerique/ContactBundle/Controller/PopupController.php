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

        $contactForm = $this->createForm('hopitalnumerique_contactbundle_popup', null, [
            'destinataires' => $request->request->get('destinataires'),
            'urlRedirection' => $request->request->get('urlRedirection'),
        ]);
        if ($request->request->has('objet')) {
            $contactForm->get('objet')->setData($request->request->get('objet'));
        }

        return $this->render(
            'HopitalNumeriqueContactBundle:Popup:index.html.twig',
            [
                'contactForm' => $contactForm->createView(),
                'destinataires' => $request->request->get('destinataires'),
            ]
        );
    }

    /**
     * Affichage de la popup.
     */
    public function inviteAction(Request $request)
    {
        if (!$request->request->has('urlRedirection')) {
            throw new \Exception('L\'URL de redirection doit être précisée.');
        }

        $inviteForm = $this->createForm('hopitalnumerique_contactbundle_popup_invite', null, [
            'urlRedirection' => $request->request->get('urlRedirection'),
            'idGroupe' => $request->request->get('idGroupe'),
        ]);

        return $this->render(
            'HopitalNumeriqueContactBundle:Popup:invite.html.twig',
            [
                'inviteForm' => $inviteForm->createView(),
            ]
        );
    }

    /**
     * Lorsque le formulaire de la popup de d'invitation est soumis.
     */
    public function submitInviteAction(Request $request)
    {
        $form = $request->request->get('hopitalnumerique_contactbundle_popup_invite');

        if (!empty($form['destinataires'])) {
            $destinataires = explode(',', $form['destinataires']);
            $regex = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,3}$/';
            foreach ($destinataires as $destinataire) {
                if (!preg_match($regex, $destinataire)) {
                    $this->get('session')->getFlashBag()->add('danger', 'Vérifiez le format des adresses e-mail renseignées.');

                    return $this->redirect($form['urlRedirection']);
                }
            }

            $groupe = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findOneBy(['id' => $form['idGroupe']]);
            if ($groupe) {
                $nomGroupe = $groupe->getTitre();
            } else {
                $nomGroupe = '';
            }

            $this->get('nodevo_mail.manager.mail')->sendInvitationMail($this->getUser(), $destinataires, $nomGroupe);

            $this->get('session')->getFlashBag()->add('success', 'Votre invitation a été envoyée.');
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'Invitation non envoyée.');
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

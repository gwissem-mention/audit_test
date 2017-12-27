<?php

namespace HopitalNumerique\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Popup de contact.
 */
class PopupController extends Controller
{
    /**
     * Affichage de la popup.
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
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
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
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

        $ajoutMembreForm = null;
        if ($request->request->get('idGroupe')) {
            $groupe = $this->get('hopitalnumerique_communautepratique.repository.groupe')->find($request->request->get('idGroupe'));

            if ($this->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAddMembre($groupe)) {
                $ajoutMembreForm = $this->createForm(
                    'hopitalnumerique_communautepratiquebundle_user_ajout',
                    null,
                    [
                        'groupe' => $groupe,
                        'action' => $this->generateUrl('hopitalnumerique_communautepratique_user_add', ['group' => $groupe->getId()]),
                    ]
                );
            }
        }

        return $this->render(
            'HopitalNumeriqueContactBundle:Popup:invite.html.twig',
            [
                'inviteForm' => $inviteForm->createView(),
                'ajoutMembreForm' => $ajoutMembreForm ? $ajoutMembreForm->createView() : null,
            ]
        );
    }

    /**
     * Lorsque le formulaire de la popup de d'invitation est soumis.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function submitInviteAction(Request $request)
    {
        $form = $request->request->get('hopitalnumerique_contactbundle_popup_invite');

        if (!empty($form['destinataires'])) {
            $destinataires = explode(',', $form['destinataires']);
            $regex = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,3}$/';
            foreach ($destinataires as $destinataire) {
                if (!preg_match($regex, $destinataire)) {
                    $this->addFlash('danger', 'Vérifiez le format des adresses e-mail renseignées.');

                    return $this->redirect($form['urlRedirection']);
                }
            }

            $groupe = $this->get('hopitalnumerique_communautepratique.manager.groupe')->findOneBy(
                ['id' => $form['idGroupe']]
            );

            if ($groupe) {
                $nomGroupe = $groupe->getTitre();
            } else {
                $nomGroupe = '';
            }

            $domain = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();

            $cdpArticleUrl = $this->generateUrl('hopital_numerique_publication_publication_article', [
                'id' => $domain->getCommunautePratiqueArticle()->getId(),
                'alias' => $domain->getCommunautePratiqueArticle()->getAlias(),
                'categorie' => 'article',
            ], RouterInterface::ABSOLUTE_URL);

            $this
                ->get('nodevo_mail.manager.mail')
                ->sendInvitationMail(
                    $this->getUser(),
                    $destinataires,
                    $nomGroupe,
                    $domain->getAdresseMailContact(),
                    $cdpArticleUrl
                )
            ;

            $this->addFlash('success', 'Votre invitation a été envoyée.');
        } else {
            $this->addFlash('danger', 'Invitation non envoyée.');
        }

        return $this->redirect($form['urlRedirection']);
    }

    /**
     * Lorsque le formulaire de la popup de contact est soumis.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function submitAction(Request $request)
    {
        $contactForm = $this->createForm('hopitalnumerique_contactbundle_popup');
        $contactForm->handleRequest($request);

        if ($contactForm->isValid()) {
            $this->addFlash('success', 'Votre message a été envoyé.');
        } else {
            $this->addFlash('danger', 'Message non envoyé.');
        }

        return $this->redirect($contactForm->get('urlRedirection')->getData());
    }
}

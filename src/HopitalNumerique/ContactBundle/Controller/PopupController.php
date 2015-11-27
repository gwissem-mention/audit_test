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

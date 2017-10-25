<?php

namespace Nodevo\MailBundle\Controller;

use Nodevo\MailBundle\Entity\Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Mail controller.
 */
class MailController extends Controller
{
    /**
     * Affiche la liste des Mails.
     *
     * @return Response
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_mail.grid.mail');
        $manager = $this->get('nodevo_mail.manager.mail');

        return $grid->render('NodevoMailBundle:Mail:index.html.twig', [
                'allowAdd' => $manager->isAllowedToAdd(),
            ]
        );
    }

    /**
     * Affiche le formulaire d'ajout de Mail.
     */
    public function addAction()
    {
        /** @var Mail $mail */
        $mail = $this->get('nodevo_mail.manager.mail')->createEmpty();

        return $this->renderForm('nodevo_mail_mail', $mail, 'NodevoMailBundle:Mail:edit.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Mail.
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function editAction($id)
    {
        //Récupération de l'entité passée en paramètre
        /** @var Mail $mail */
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy(['id' => $id]);

        return $this->renderForm('nodevo_mail_mail', $mail, 'NodevoMailBundle:Mail:edit.html.twig');
    }

    /**
     * Affiche le Mail en fonction de son ID passé en paramètre.
     *
     * @param $id
     *
     * @return Response
     */
    public function showAction($id)
    {
        //Récupération de l'entité en fonction du paramètre
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy(['id' => $id]);

        return $this->render('NodevoMailBundle:Mail:show.html.twig', [
            'mail' => $mail,
            'allowDelete' => $this->get('nodevo_mail.manager.mail')->isAllowedToDelete(),
        ]);
    }

    /**
     * Suppresion d'un Mail.
     *
     * @METHOD = POST|DELETE
     *
     * @param $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $mail = $this->get('nodevo_mail.manager.mail')->findOneBy(['id' => $id]);

        //Suppression de l'entitée
        $this->get('nodevo_mail.manager.mail')->delete($mail);

        $this->addFlash('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $this->generateUrl('nodevo_mail_mail') . '"}', 200);
    }

    /**
     * Test d'envoi d'un Mail.
     *
     * @param $id
     *
     * @return RedirectResponse
     */
    public function sendTestAction($id)
    {
        $user = $this->getUser();
        $mail = $this->get('nodevo_mail.manager.mail')->sendMessageTest($id, $user);

        $this->get('mailer')->send($mail);

        $message = 'Message de test envoyé à ' . $this->get('nodevo_mail.manager.mail')->getDestinataire();
        $this->addFlash('info', $message);

        return $this->redirect($this->generateUrl('nodevo_mail_mail_show', ['id' => $id]));
    }

    /**
     * Redirect user to the right domain when he came from a link in a mail
     * @param string $pathEncoded
     *
     * @return RedirectResponse
     */
    public function redirectAction($pathEncoded)
    {
        list($type, $entityId, $userId) = explode('/', base64_decode($pathEncoded));

        // Actually useless but will be soon useful with practice community
        switch($type) {
            case 'publication':
                $url = $this->get('router')->generate('hopital_numerique_publication_publication_objet', [
                    'id' => $entityId
                ]);
                $entityDomains = $this->get('hopitalnumerique_objet.repository.objet')->findOneById($entityId)->getDomaines()->toArray();
                break;
        }

        $baseUrl = $this->get('hopitalnumerique_domaine.service.base_url_provider')->getBaseUrl(
            $entityDomains,
            $this->get('hopitalnumerique_domaine.manager.domaine')->getDomainesUserConnected($userId)
        );

        return $this->redirect($baseUrl . $url);
    }

    /**
     * Effectue le render du formulaire Mail.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Mail   $mail     Entité Mail
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return RedirectResponse|Response
     */
    private function renderForm($formName, $mail, $view)
    {
        //Création du formulaire via le service
        $form = $this->createForm($formName, $mail);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($mail->getId()) ? true : false;

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_mail.manager.mail')->save($mail);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->addFlash(($new ? 'success' : 'info'), 'Template d\'e-mail ' . ($new ? 'ajouté.' : 'mis à jour.'));

                $do = $request->request->get('do');

                return $this->redirect(($do == 'save-close' ? $this->generateUrl('nodevo_mail_mail') : $this->generateUrl('nodevo_mail_mail_edit', ['id' => $mail->getId()])));
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'mail' => $mail,
            'allowDelete' => $this->get('nodevo_mail.manager.mail')->isAllowedToDelete(),
        ]);
    }
}

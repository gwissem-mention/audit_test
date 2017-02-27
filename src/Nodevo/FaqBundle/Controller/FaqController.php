<?php

namespace Nodevo\FaqBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Faq controller.
 */
class FaqController extends Controller
{
    /**
     * Affiche la liste des Faq.
     */
    public function indexAction()
    {
        $grid = $this->get('nodevo_faq.grid.faq');

        return $grid->render('NodevoFaqBundle:Faq:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Faq.
     */
    public function addAction()
    {
        $faq = $this->get('nodevo_faq.manager.faq')->createEmpty();

        return $this->renderForm('nodevo_faq_faq', $faq, 'NodevoFaqBundle:Faq:edit.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Faq.
     *
     * @param int $id id de Faq
     */
    public function editAction($id)
    {
        //Récupération de l'entité passée en paramètre
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy(['id' => $id]);

        return $this->renderForm('nodevo_faq_faq', $faq, 'NodevoFaqBundle:Faq:edit.html.twig');
    }

    /**
     * Affiche le Faq en fonction de son ID passé en paramètre.
     *
     * @param int $id id de Faq
     */
    public function showAction($id)
    {
        //Récupération de l'entité en fonction du paramètre
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy(['id' => $id]);

        return $this->render('NodevoFaqBundle:Faq:show.html.twig', [
            'faq' => $faq,
        ]);
    }

    /**
     * Suppresion d'un Faq.
     *
     * @param int $id Id de Faq.
     *                METHOD = POST|DELETE
     */
    public function deleteAction($id)
    {
        $faq = $this->get('nodevo_faq.manager.faq')->findOneBy(['id' => $id]);

        //Suppression de l'entitée
        $this->get('nodevo_faq.manager.faq')->delete($faq);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $this->generateUrl('nodevo_faq_faq') . '"}', 200);
    }

    /**
     * Suppression de masse des faq.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('nodevo_faq.grid.faq')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $faqs = $this->get('nodevo_faq.manager.faq')->findBy(['id' => $primaryKeys]);

        //Suppression de l'etablissement
        $this->get('nodevo_faq.manager.faq')->delete($faqs);
        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->generateUrl('nodevo_faq_faq'));
    }

    /**
     * Effectue le render du formulaire Faq.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Faq    $entity   Entité $faq
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm($formName, $faq, $view)
    {
        //Création du formulaire via le service
        $form = $this->createForm($formName, $faq);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($faq->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('nodevo_faq.manager.faq')->save($faq);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add(($new ? 'success' : 'info'), 'Elément de FAQ ' . ($new ? 'ajouté.' : 'mis à jour.'));

                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');

                return $this->redirect(($do == 'save-close' ? $this->generateUrl('nodevo_faq_faq') : $this->generateUrl('nodevo_faq_faq_edit', ['id' => $faq->getId()])));
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'faq' => $faq,
        ]);
    }
}

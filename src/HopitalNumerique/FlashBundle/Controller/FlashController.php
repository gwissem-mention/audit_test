<?php

namespace HopitalNumerique\FlashBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Flash controller.
 */
class FlashController extends Controller
{
    /**
     * Affiche la liste des Flash.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_flash.grid.flash');

        return $grid->render('HopitalNumeriqueFlashBundle:Flash:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Flash.
     */
    public function addAction()
    {
        $flash = $this->get('hopitalnumerique_flash.manager.flash')->createEmpty();

        return $this->renderForm('hopitalnumerique_flash_flash', $flash, 'HopitalNumeriqueFlashBundle:Flash:edit.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Flash.
     *
     * @param int $id id de Flash
     */
    public function editAction($id)
    {
        //Récupération de l'entité passée en paramètre
        $flash = $this->get('hopitalnumerique_flash.manager.flash')->findOneBy(['id' => $id]);

        return $this->renderForm('hopitalnumerique_flash_flash', $flash, 'HopitalNumeriqueFlashBundle:Flash:edit.html.twig');
    }

    /**
     * Suppresion d'un Flash.
     *
     * @param int $id Id de Flash.
     *                METHOD = POST|DELETE
     */
    public function deleteAction($id)
    {
        $flash = $this->get('hopitalnumerique_flash.manager.flash')->findOneBy(['id' => $id]);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_flash.manager.flash')->delete($flash);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_flash_flash') . '"}', 200);
    }

    /**
     * Suppression de masse des flashes.
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
            $rawDatas = $this->get('hopitalnumerique_flash.manager.flash')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $flashes = $this->get('hopitalnumerique_flash.manager.flash')->findBy(['id' => $primaryKeys]);

        $this->get('hopitalnumerique_flash.manager.flash')->delete($flashes);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopitalnumerique_flash_flash'));
    }

    /**
     * Effectue le render du formulaire Flash.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Flash  $entity   Entité $flash
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm($formName, $flash, $view)
    {
        //Création du formulaire via le service
        $form = $this->createForm($formName, $flash);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($flash->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_flash.manager.flash')->save($flash);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add(($new ? 'success' : 'info'), 'Flash ' . ($new ? 'ajouté.' : 'mis à jour.'));

                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');

                return $this->redirect(($do == 'save-close' ? $this->generateUrl('hopitalnumerique_flash_flash') : $this->generateUrl('hopitalnumerique_flash_flash_edit', ['id' => $flash->getId()])));
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'flash' => $flash,
        ]);
    }
}

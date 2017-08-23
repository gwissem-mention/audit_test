<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Form\RechercheParcoursGestionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RechercheParcoursGestion controller.
 */
class RechercheParcoursGestionController extends Controller
{
    /**
     * Affiche la liste des RechercheParcoursGestion.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_rechercheparcours.grid.rechercheparcoursgestion');

        return $grid->render('HopitalNumeriqueRechercheParcoursBundle:rechercheParcoursGestion:index.html.twig');
    }

    /**
     * @param RechercheParcoursGestion $rechercheParcoursGestion
     *
     * @return Response
     */
    public function editAction(Request $request, RechercheParcoursGestion $rechercheParcoursGestion = null)
    {
        $validationGroups = ['Default'];
        if (!is_null($rechercheParcoursGestion)) {
            $validationGroups[] = 'update';
            $editRechercheParcoursGestionCommand = $this->get('hopitalnumerique_rechercheparcours.hydrator.edit_recherche_parcours_gestion_command')->createFromEntity($rechercheParcoursGestion);
        } else {
            $editRechercheParcoursGestionCommand = $this->get('hopitalnumerique_rechercheparcours.hydrator.edit_recherche_parcours_gestion_command')->createEmpty();
        }

        $form = $this->createForm(RechercheParcoursGestionType::class, $editRechercheParcoursGestionCommand, [
            'validation_groups' => $validationGroups,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rechercheParcoursGestion = $this->get('hopitalnumerique_rechercheparcours.handler.edit_recherche_parcours_gestion_command')->handle($editRechercheParcoursGestionCommand);

            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            if ($editRechercheParcoursGestionCommand->update) {
                $this->get('session')
                    ->getFlashBag()
                    ->add('info', 'Gestionnaire de recherche par parcours mis à jour.')
                ;
            } else {
                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Gestionnaire de recherche par parcours ajouté.')
                ;
            }

            //on redirige vers la page index ou la page edit selon le bouton utilisé
            $do = $request->request->get('do');

            return $this->redirect(($do == 'save-close' ? $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion') : $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_edit', ['rechercheParcoursGestion' => $rechercheParcoursGestion->getId()])));
        }

        $serviceHistoryReader = $this->get('hopitalnumerique_rechercheparcours.guided_search_history_reader');

        if ($rechercheParcoursGestion) {
            $updates = $serviceHistoryReader->getHistory($rechercheParcoursGestion);
        }
        else {
            $updates = array();
        }

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:rechercheParcoursGestion:edit.html.twig', [
            'form' => $form->createView(),
            'rechercheparcoursgestion' => $editRechercheParcoursGestionCommand,
            'updates' => $updates,
        ]);
    }

    /**
     * Affiche le RechercheParcoursGestion en fonction de son ID passé en paramètre.
     *
     * @param int $id id de RechercheParcoursGestion
     */
    public function showAction($id)
    {
        //Récupération de l'entité en fonction du paramètre
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy(['id' => $id]);

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:show.html.twig', [
            'rechercheparcoursgestion' => $rechercheparcoursgestion
        ]);
    }

    /**
     * Suppresion d'un RechercheParcoursGestion.
     *
     * @param int $id Id de RechercheParcoursGestion.
     *                METHOD = POST|DELETE
     */
    public function deleteAction($id)
    {
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy(['id' => $id]);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->delete($rechercheparcoursgestion);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new Response('{"success":true, "url" : "' . $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion') . '"}', 200);
    }

    /**
     * Actuib de masse de suppression.
     *
     * @param [type] $primaryKeys    [description]
     * @param [type] $allPrimaryKeys [description]
     *
     * @return [type]
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $rechercheParcoursGestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findBy(['id' => $primaryKeys]);

        $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->delete($rechercheParcoursGestion);

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return $this->redirect($this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion'));
    }
}

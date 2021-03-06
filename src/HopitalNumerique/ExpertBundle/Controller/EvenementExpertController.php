<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\ExpertBundle\Entity\EvenementExpert;

/**
 * EvenementExpert controller.
 */
class EvenementExpertController extends Controller
{
    /**
     * Affiche la liste des EvenementExpert.
     */
    public function indexAction(ActiviteExpert $activiteExpert)
    {
        $grid = $this->get('hopitalnumerique_expert.grid.evenementexpert');
        $grid->setSourceCondition('module', $activiteExpert->getId());

        return $grid->render('HopitalNumeriqueExpertBundle:EvenementExpert:index.html.twig', ['activiteExpert' => $activiteExpert]);
    }

    /**
     * Affiche le formulaire d'ajout de EvenementExpert.
     */
    public function addAction(ActiviteExpert $activiteExpert)
    {
        $evenementexpert = $this->get('hopitalnumerique_expert.manager.evenementexpert')->createEmpty();
        $evenementexpert->setActivite($activiteExpert);

        return $this->renderForm('hopitalnumerique_expert_evenementexpert', $evenementexpert, 'HopitalNumeriqueExpertBundle:EvenementExpert:edit.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de EvenementExpert.
     *
     * @param int $id id de EvenementExpert
     */
    public function editAction($id)
    {
        //Récupération de l'entité passée en paramètre
        $evenementexpert = $this->get('hopitalnumerique_expert.manager.evenementexpert')->findOneBy(['id' => $id]);

        return $this->renderForm('hopitalnumerique_expert_evenementexpert', $evenementexpert, 'HopitalNumeriqueExpertBundle:EvenementExpert:edit.html.twig');
    }

    /**
     * POPIN : Partage de resultat.
     */
    public function parametrageAction(EvenementExpert $evenementExpert)
    {
        return $this->render('HopitalNumeriqueExpertBundle:EvenementExpert:fancy.html.twig', [
            'evenementExpert' => $evenementExpert,
        ]);
    }

    /**
     * POPIN : gestion de la présence des experts.
     */
    public function parametrageSaveAction(EvenementExpert $evenementExpert, Request $request)
    {
        //Mise à jour de la présence des experts
        $expertsId = json_decode($this->get('request')->request->get('experts'));

        $presences = $this->get('hopitalnumerique_expert.manager.evenementpresenceexpert')->findBy(['evenement' => $evenementExpert]);

        foreach ($presences as &$presence) {
            $presence->setDate(new \DateTime());
            $presence->setPresent(in_array($presence->getExpertConcerne()->getId(), $expertsId));
        }

        $this->get('hopitalnumerique_expert.manager.evenementpresenceexpert')->save($presences);

        return new Response('{"success":true}', 200);
    }

    /**
     * Impression du pdf de la fiche de présence.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function impressionFichePresenceAction(EvenementExpert $evenementExpert)
    {
        $html = $this->renderView('HopitalNumeriqueExpertBundle:EvenementExpert/pdf:fichePresence.html.twig', [
            'evenementExpert' => $evenementExpert,
        ]);

        $options = [
            'margin-bottom' => 10,
            'margin-left' => 4,
            'margin-right' => 4,
            'margin-top' => 10,
            'encoding' => 'UTF-8',
            'orientation' => 'Landscape',
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $options, true),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Fiche_presence_' . $evenementExpert->getActivite()->getTitre() . '_' . $evenementExpert->getNom()->getLibelle() . '_' . $evenementExpert->getDate()->format('d/m/Y') . '.pdf"',
            ]
        );
    }

    /**
     * Effectue le render du formulaire EvenementExpert.
     *
     * @param string          $formName Nom du service associé au formulaire
     * @param EvenementExpert $entity   Entité $evenementexpert
     * @param string          $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm($formName, $evenementexpert, $view)
    {
        //Création du formulaire via le service
        $form = $this->createForm($formName, $evenementexpert);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($evenementexpert->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_expert.manager.evenementexpert')->save($evenementexpert);
                $this->get('hopitalnumerique_expert.manager.evenementpresenceexpert')->majExperts($evenementexpert);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add(($new ? 'success' : 'info'), 'EvenementExpert ' . ($new ? 'ajouté.' : 'mis à jour.'));

                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');

                return $this->redirect(($do == 'save-close' ? $this->generateUrl('hopitalnumerique_expert_evenement_expert', ['id' => $evenementexpert->getActivite()->getId()]) : $this->generateUrl('hopitalnumerique_expert_evenement_expert_edit', ['id' => $evenementexpert->getId()])));
            }
        }

        return $this->render($view, [
            'form' => $form->createView(),
            'evenementexpert' => $evenementexpert,
            'activiteExpert' => $evenementexpert->getActivite(),
        ]);
    }
}

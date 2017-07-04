<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\RechercheBundle\Form\Type\RequeteType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RequeteController
 */
class RequeteController extends Controller
{
    /**
     * Affichage de la liste des requêtes de l'utilisateur connecté.
     *
     * @param Request $request
     * @param $indexVue
     *
     * @return Response
     */
    public function indexAction(Request $request, $indexVue)
    {
        //get connected user
        $user = $this->getUser();
        $domaineId = $request->getSession()->get('domaineId');

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy([
            'user' => $user,
            'domaine' => $domaineId,
        ]);

        $consultations = $this->get('hopitalnumerique_objet.manager.consultation')
            ->getLastsConsultations($user, $domaineId)
        ;

        if ($indexVue) {
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:index.html.twig', [
                'requetes' => $requetes,
                'consultations' => $consultations,
            ]);
        } else {
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:mesrequetes.html.twig', [
                'requetes' => $requetes,
            ]);
        }
    }

    /**
     * @param Request $request
     * @param Requete $search
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Requete $search)
    {
        if ($this->getUser()->getId() !== $search->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        try {
            $this->get('doctrine.orm.entity_manager')->remove($search);

            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (\Exception $exception) {
            $this->addFlash('danger', $this->get('translator')->trans('saved_searches.delete.error', [], 'widget'));

            return $this->redirect($request->headers->get('referer'));
        }

        $this->addFlash('success', $this->get('translator')->trans('saved_searches.delete.success', [], 'widget'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Delete d'une requete (AJAX).
     *
     * @param int $id ID de la requete à supprimer
     *
     * @return Response
     */
    public function ajaxDeleteAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy(['id' => $id]);

        //get connected user
        $user = $this->getUser();

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->delete($requete);

        //si on a supprimé la dernière requete par défaut, on met en défaut une autre requete
        if ($requete->isDefault()) {
            $newRequete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy(['user' => $user]);
            if ($newRequete) {
                $newRequete->setDefault(true);
                $this->get('hopitalnumerique_recherche.manager.requete')->save($newRequete);
            }
        }

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        return new JsonResponse(['success' => true, 'url' => $this->generateUrl('hopital_numerique_requete_homepage')]);
    }

    /**
     * Edition du nom d'une requete (AJAX).
     *
     * @param int $id ID de la requete à mettre à jour
     *
     * @return Response
     */
    public function editAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy(['id' => $id]);
        $nom = $this->get('request')->request->get('nom');
        $requete->setNom($nom);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->save($requete);

        $this->get('session')->getFlashBag()->add('info', 'Recherche mise à jour avec succès.');

        return new JsonResponse(['success' => true, 'url' => $this->generateUrl('hopital_numerique_requete_homepage')]);
    }

    /**
     * Toggle Default d'une requete (AJAX).
     *
     * @param int $id ID de la requete à toggle
     *
     * @return Response
     */
    public function toggleAction($id)
    {
        //get connected user
        $user = $this->getUser();

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy(['user' => $user]);

        /** @var Requete $requete */
        foreach ($requetes as $requete) {
            $isDefault = ($requete->getId() == $id);
            $requete->setDefault($isDefault);
        }
        $this->get('hopitalnumerique_recherche.manager.requete')->save($requetes);

        $this->get('session')->getFlashBag()->add('info', 'Recherche par défaut modifiée avec succès.');

        return new JsonResponse(['success' => true, 'url' => $this->generateUrl('hopital_numerique_requete_homepage')]);
    }

    /**
     * @param Request   $request
     * @param Requete   $search
     *
     * @return JsonResponse
     */
    public function changeNameAction(Request $request, Requete $search)
    {
        if ($this->getUser()->getId() !== $search->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(RequeteType::class, $search, [
            'csrf_protection' => false,
        ]);

        $form->submit([
            'nom' => $request->request->get('text'),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->flush($search);
        }

        return new JsonResponse();
    }
}

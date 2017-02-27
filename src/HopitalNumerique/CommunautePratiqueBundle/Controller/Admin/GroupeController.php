<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Admin;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur des groupes dans l'admin.
 */
class GroupeController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche les groupes à gérer.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $groupeGrid = $this->get('hopitalnumerique_communautepratique.grid.groupe');

        return $groupeGrid->render('HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:list.html.twig');
    }

    /**
     * Ajoute un groupe.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $nouveauGroupe = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->createEmpty();

        if ($request->query->has('domaine')) {
            $nouveauGroupe->setDomaine($this->container->get('hopitalnumerique_domaine.manager.domaine')->findOneById(intval($request->query->getInt('domaine'))));
        } elseif ($request->request->has('hopitalnumerique_communautepratiquebundle_groupe')) {
            $formPost = $request->request->get('hopitalnumerique_communautepratiquebundle_groupe');

            return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_add', ['domaine' => intval($formPost['domaine'])]));
        }

        return $this->editAction($nouveauGroupe, $request);
    }

    /**
     * Édite un groupe.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Groupe $groupe, Request $request)
    {
        $groupeForm = $this->createForm('hopitalnumerique_communautepratiquebundle_groupe', $groupe);
        $groupeForm->handleRequest($request);

        if ($groupeForm->isSubmitted()) {
            if ($groupeForm->isValid()) {
                $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->save($groupe);
                $this->get('session')->getFlashBag()->add('success', 'Groupe enregistré.');
                $do = $this->container->get('request')->request->get('do');

                return $this->redirect($do == 'save-close' ? $this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_list') : $this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_edit', ['id' => $groupe->getId()]));
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Groupe non enregistré.');
            }
        }

        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:edit.html.twig',
            [
                'groupeForm' => $groupeForm->createView(),
                'groupe' => $groupe,
            ]
        );
    }

    /**
     * Supprime en masse les groupes.
     *
     * @param int[] $primaryKeys Les ID des groupes à supprimer
     *
     * @return \Component\HttpFoundation\RedirectResponse Redirection vers la liste
     */
    public function deleteMassAction(array $primaryKeys)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();

        if ($this->container->get('nodevo_acl.manager.acl')->checkAuthorization($this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_deletemass'), $utilisateurConnecte) != -1) {
            $groupes = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findBy(['id' => $primaryKeys]);
            $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->delete($groupes);

            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
        } else {
            $this->get('session')->getFlashBag()->add('warning', 'Vous ne possédez pas les droits nécessaires pour supprimer des groupes.');
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_communautepratique_admin_groupe_list'));
    }
}

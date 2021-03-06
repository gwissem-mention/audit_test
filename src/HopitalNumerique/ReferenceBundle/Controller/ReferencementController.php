<?php

namespace HopitalNumerique\ReferenceBundle\Controller;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Referencement controller.
 */
class ReferencementController extends Controller
{
    /**
     * Affiche la fenêtre contenant le référencement d'une entité.
     *
     * @param      $entityType
     * @param      $entityId
     * @param bool $redirect
     *
     * @return Response
     * @throws \Exception
     */
    public function popinAction($entityType, $entityId, $redirect = true)
    {
        $entityService = $this->get('hopitalnumerique_core.dependency_injection.entity');

        $entity = $entityService->getEntityByTypeAndId($entityType, $entityId);
        $entityTitle = $entityService->getTitleByEntity($entity, null, false);

        if (!$this->isGranted('reference', $entity)) {
            throw $this->createAccessDeniedException();
        }

        if (null === $entity) {
            throw new \Exception('Entité non trouvée pour TYPE = "' . $entityType . '" et ID = "' . $entityId . '".');
        }

        $domaines = [];

        foreach ($entityService->getDomainesByEntity($entity) as $domaine) {
            if ($this->getUser()->hasDomaine($domaine)) {
                $domaines[] = $domaine;
            }
        }

        $referencesTree = $this->get('hopitalnumerique_reference.dependency_injection.referencement')
           ->getReferencesTreeWithEntitiesHasReferences(
               $domaines,
               $entityType,
               $entityId
           )
        ;

        return $this->render(
            'HopitalNumeriqueReferenceBundle:Referencement:popin.html.twig',
            [
                'entityType'     => $entityType,
                'entityId'       => $entityId,
                'entityTitle'    => $entityTitle,
                'referencesTree' => $referencesTree,
                'redirectionUrl' => $redirect === true
                    ? $entityService
                       ->getMangementUrlByEntity($entity)
                    : null,
            ]
        );
    }

    /**
     * @return Response
     */
    public function userPopinAction()
    {
        if (!$this->getUser() instanceof User) {
            throw new AccessDeniedHttpException();
        }

        return $this->popinAction(Entity::ENTITY_TYPE_AMBASSADEUR, $this->getUser()->getId());
    }

    /**
     * Enregistre les EntityHasReference de la popin.
     *
     * @param Request $request
     * @param int     $entityType Type d'entité
     * @param int     $entityId   ID de l'entité
     *
     * @return JsonResponse
     */
    public function saveChosenReferencesAction(Request $request, $entityType, $entityId)
    {
        /**
         * @var array Les EntityHasReference de la popin
         */
        $entitiesHaveReferencesParameters = $request->request->get('entitiesHaveReferencesParameters');

        $entity = $this->get('hopitalnumerique_core.dependency_injection.entity')->getEntityByTypeAndId(
            $entityType,
            $entityId
        );

        if (!$this->isGranted('reference', $entity)) {
            throw $this->createAccessDeniedException();
        }

        $referencesDomainesToDelete = $this->getDomainesToDeleteForNoteSaving($entity);
        $references = $this->get('hopitalnumerique_reference.manager.entity_has_reference')
            ->findByEntityTypeAndEntityIdAndDomaines(
                $entityType,
                $entityId,
                $referencesDomainesToDelete
            )
        ;

        $this->get('hopitalnumerique_reference.manager.entity_has_reference')->delete($references);

        if (null !== $entitiesHaveReferencesParameters) {
            foreach ($entitiesHaveReferencesParameters as $entityHasReferenceParameters) {
                $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(
                    $entityHasReferenceParameters['referenceId']
                );
                $entityHasReference = $this->get('hopitalnumerique_reference.manager.entity_has_reference')->findOneBy([
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'reference' => $reference,
                ]);
                if (null === $entityHasReference) {
                    $entityHasReference = $this->get('hopitalnumerique_reference.manager.entity_has_reference')
                        ->createEmpty()
                    ;
                    $entityHasReference->setEntityType($entityType);
                    $entityHasReference->setEntityId($entityId);
                    $entityHasReference->setReference($reference);
                }

                $entityHasReference->setPrimary('1' == $entityHasReferenceParameters['primary']);
                $this->get('hopitalnumerique_reference.manager.entity_has_reference')->save($entityHasReference);
            }
        }

        $this->get('hopitalnumerique_reference.doctrine.referencement.note_saver')->saveScoresForEntityTypeAndEntityId(
            $entityType,
            $entityId
        );

        $this->addFlash('success', 'Références enregistrées.');

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Lors de l'enregistrement des références d'une entité, on ré-initialise les références existantes en
     * supprimant celles du domaine de l'utilisateur connecté et celles n'appartenant plus à l'entité.
     *
     * @param object $entity
     *
     * @return array
     */
    private function getDomainesToDeleteForNoteSaving($entity)
    {
        $domaines = [];
        $userDomaines = $this->getUser()->getDomaines();
        $entityDomaines = $this->get('hopitalnumerique_core.dependency_injection.entity')->getDomainesByEntity($entity);

        foreach ($this->get('hopitalnumerique_domaine.manager.domaine')->findAll() as $domaine) {
            $userHasDomaine = false;
            foreach ($userDomaines as $userDomaine) {
                if ($userDomaine->equals($domaine)) {
                    $userHasDomaine = true;
                    break;
                }
            }

            if ($userHasDomaine) {
                $domaines[] = $domaine;
            } else {
                $entityHasDomaine = false;
                foreach ($entityDomaines as $entityDomaine) {
                    if ($entityDomaine->equals($domaine)) {
                        $entityHasDomaine = true;
                        break;
                    }
                }

                if (!$entityHasDomaine) {
                    $domaines[] = $domaine;
                }
            }
        }

        return $domaines;
    }

    /**
     * Cron qui met à jour toutes les notes du référencement.
     *
     * @param $token
     *
     * @return Response
     */
    public function cronSaveNotesAction($token)
    {
        if ($token === 'PBYDHWURJYILOLP24FKGMERO78HD7SUXVRT') {
            set_time_limit(0);
            foreach ($this->get('hopitalnumerique_domaine.manager.domaine')->findAll() as $domaine) {
                $this->get('hopitalnumerique_reference.doctrine.referencement.note_saver')->saveScoresForDomaine(
                    $domaine
                );
            }

            return new Response('Cron termin&eacute; !');
        }

        return new Response('NOK :(');
    }

    /**
     * Cron qui supprime les entités qui n'existent pas.
     *
     * @param $token
     *
     * @return Response
     */
    public function cronRemoveInexistantsAction($token)
    {
        if ($token === 'gfd5g6df81df6gdf1g6fd1scd8s6f') {
            set_time_limit(0);
            $this->get('hopitalnumerique_reference.doctrine.referencement.deleter')->removeInexistants();

            return new Response('Cron termin&eacute; !');
        }

        return new Response('NOK :(');
    }

    /**
     * Migre les anciennes données.
     *
     * @param $token
     *
     * @return Response
     */
    public function migreAction($token)
    {
        if ('kawabunga' == $token) {
            $this->get('hopitalnumerique_reference.doctrine.referencement.migration')->migreAll();

            return new Response('OK');
        }

        return new Response('Ah non non non.');
    }
}

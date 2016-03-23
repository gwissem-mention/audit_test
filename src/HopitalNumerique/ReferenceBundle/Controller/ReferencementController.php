<?php
namespace HopitalNumerique\ReferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Referencement controller.
 */
class ReferencementController extends Controller
{
    /**
     * Affiche la fenêtre contenant le référencement d'une entité.
     *
     * @param object $entity Entité
     */
    public function popinAction($entityType, $entityId)
    {
        $entity = $this->container->get('hopitalnumerique_reference.dependency_injection.referencement.entity')->getEntityByTypeAndId($entityType, $entityId);
        if (null === $entity) {
            throw new \Exception('Entité non trouvée pour TYPE = "'.$entityType.'" et ID = "'.$entityId.'".');
        }

        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.referencement')->getReferencesTreeWithEntitiesHasReferences(
            $this->container->get('hopitalnumerique_reference.dependency_injection.referencement.entity')->getDomainesByEntity($entity),
            $entityType,
            $entityId
        );

        return $this->render('HopitalNumeriqueReferenceBundle:Referencement:popin.html.twig', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'referencesTree' => $referencesTree
        ));
    }

    /**
     * Enregistre les EntityHasReference de la popin.
     *
     * @param string  $entityType                       Type d'entité
     * @param integer $entityId                         ID de l'entité
     */
    public function saveChosenReferencesAction(Request $request, $entityType, $entityId)
    {
        /**
         * @var array Les EntityHasReference de la popin
         */
        $entitiesHaveReferencesParameters = $request->request->get('entitiesHaveReferencesParameters');

        $references = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);
        $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->delete($references);

        if (null !== $entitiesHaveReferencesParameters) {
            foreach ($entitiesHaveReferencesParameters as $entityHasReferenceParameters) {
                $entityHasReference = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->createEmpty();
                $entityHasReference->setEntityType($entityType);
                $entityHasReference->setEntityId($entityId);
                $entityHasReference->setReference($this->container->get('hopitalnumerique_reference.manager.reference')->findOneById($entityHasReferenceParameters['referenceId']));
                $entityHasReference->setPrimary('1' == $entityHasReferenceParameters['primary']);
                $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->save($entityHasReference);
            }
        }

        $this->addFlash('success', 'Références enregistrées.');

        return new JsonResponse(array(
            'success' => true
        ));
    }

    /**
     * Cron qui met à jour toutes les notes du référencement.
     */
    public function cronSaveNotesAction($token)
    {
        if ($token === 'PBYDHWURJYILOLP24FKGMERO78HD7SUXVRT') {
            foreach ($this->container->get('hopitalnumerique_domaine.manager.domaine')->findAll() as $domaine) {
                $this->container->get('hopitalnumerique_reference.doctrine.referencement.note_saver')->saveScoresForDomaine($domaine);
            }
        }

        return new Response('Cron termin&eacute; !');
    }
}

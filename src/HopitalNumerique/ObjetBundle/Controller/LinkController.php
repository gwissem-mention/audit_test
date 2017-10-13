<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectCommand;
use HopitalNumerique\CoreBundle\Domain\Command\Relation\LinkObjectHandler;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;

/**
 * Link controller.
 */
class LinkController extends Controller
{
    /**
     * Fancybox d'ajout d'objet à l'utilisateur.
     *
     * @param Objet $objet
     *
     * @return Response
     */
    public function addLinkAction(Objet $objet)
    {
        $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo(null, $objet);

        return $this->render('HopitalNumeriqueObjetBundle:Objet:add_link.html.twig', [
            'type' => 'object',
            'arbo' => $arbo,
            'idObjet' => $objet->getId(),
            'saveLinkUri' => $this->generateUrl('hopitalnumerique_objet_objet_saveLink')
        ]);
    }

    /**
     * Sauvegarde le lien point dur -> objets.
     */
    public function saveLinkAction()
    {
        //get posted vars
        $id = $this->get('request')->request->get('idObjet');
        $objets = $this->get('request')->request->get('objets');

        //bind Objet
        $pointDur = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(['id' => $id]);

        $linkObjectHandler = $this->get(LinkObjectHandler::class);
        foreach ($objets as $one) {

            list($type, $objectId) = explode(':', $one);

            switch ($type) {
                case 'PUBLICATION':
                case 'ARTICLE':
                    $object = $this->get('hopitalnumerique_objet.repository.objet')->find($objectId);
                    break;
                case 'INFRADOC':
                    $object = $this->get('hopitalnumerique_objet.repository.contenu')->find($objectId);
                    break;
                default:
                    throw new \LogicException(sprintf('Type {%s} not founded', $type));
            }

            $linkObjectHandler->handle(new LinkObjectCommand($pointDur, $object));
        }

        $this->get('session')->getFlashBag()->add('success', 'Les productions ont été liées au point dur.');

        return new JsonResponse(
            [
                'success' => true,
                'url' => $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $id])
            ]
        );
    }

    /**
     * @param Objet $object
     *
     * @return JsonResponse|Response
     */
    public function linkDiscussionAction(Objet $object)
    {
        if ($objects = $this->get('request')->request->get('objets')) {
            $linkObjectHandler = $this->get(LinkObjectHandler::class);
            foreach ($objects as $objectToLink) {
                $linkObjectHandler->handle(new LinkObjectCommand($object, $this->get(DiscussionRepository::class)->find($objectToLink)));
            }

            $this->get('session')->getFlashBag()->add('success', 'Les discussions ont été liées au point dur.');

            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->generateUrl('hopitalnumerique_objet_objet_edit', ['id' => $object->getId()])
                ]
            );
        }

        return $this->render('HopitalNumeriqueObjetBundle:Objet:add_link.html.twig', [
            'type' => 'discussion',
            'arbo' => $this->get(DiscussionRepository::class)->getPublicDiscussionList(),
            'idObjet' => $object->getId(),
            'saveLinkUri' => $this->generateUrl('hopitalnumerique_objet_objet_ad_discussion_link', ['object' => $object->getId()]),
        ]);
    }

    /**
     * Reordonne les productions.
     *
     * @param Objet $objet L'objet point dur
     *
     * @return Response
     */
    public function reorderAction(Objet $objet)
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        $doctrineArray = new ArrayCollection();
        foreach ($datas as $one) {
            $doctrineArray->add($one['id']);
        }

        $objet->setObjets($doctrineArray->toArray());
        $this->get('hopitalnumerique_objet.manager.objet')->save($objet);

        return new JsonResponse(['success' => true], 200);
    }
}

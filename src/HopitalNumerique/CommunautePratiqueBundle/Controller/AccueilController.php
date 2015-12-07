<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Accueil de la communauté de pratiques.
 */
class AccueilController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Accueil de la communauté de pratiques.
     */
    public function indexAction()
    {
        $messieursAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(3, $this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::CIVILITE_MONSIEUR_ID))
        ;
        $mesdamesAuHasard = $this->container->get('hopitalnumerique_user.manager.user')
            ->findCommunautePratiqueRandomMembres(3, $this->container->get('hopitalnumerique_reference.manager.reference')->findOneById(Reference::CIVILITE_MADAME_ID))
        ;
        $membresAuHasard = array_merge(
            $messieursAuHasard,
            $mesdamesAuHasard,
            $this->container->get('hopitalnumerique_user.manager.user')
                ->findCommunautePratiqueRandomMembres(3, null, array_merge($messieursAuHasard, $mesdamesAuHasard))
        );
        shuffle($membresAuHasard);
        
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Accueil:index.html.twig',
            array(
                'totalMembres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueMembresCount(),
                'membres' => $membresAuHasard
            )
        );
    }
}

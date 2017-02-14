<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * Génération manuelle d'une requete de recherche en fonction
     * d'un tableau d'id de reference passé en param, d'une recherche textuelle et de type(s)
     *
     * @param string $refs Liste des références à explode
     * @param null   $q    Recherche textuelle
     * @param null   $type Liste des références à explode
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse [type]
     */
    public function generateManuallyRequeteAction($refs = null, $q = null, $type = null)
    {
        $referenceIds = ('null' != $refs ? explode(',', $refs) : []);
        $searchedText = ($q == 'null' ? '' : $q);
        $publicationCategoryIds = ($type == "null" ? [] : explode(',', $type));

        $referencementRequeteSession = $this->get(
            'hopitalnumerique_recherche.dependency_injection.referencement.requete_session'
        );

        $referencementRequeteSession->setReferenceIds($referenceIds);
        $referencementRequeteSession->setPublicationCategoryIds($publicationCategoryIds);
        $referencementRequeteSession->setSearchedText($searchedText);

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }
}

<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use Symfony\Component\Routing\RouterInterface;

class Router
{
    /**
     * @var CurrentDomaine
     */
    protected $currentDomaineProvider;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * CommunautePratique constructor.
     *
     * @param CurrentDomaine  $currentDomaineProvider
     * @param RouterInterface $router
     */
    public function __construct(CurrentDomaine $currentDomaineProvider, RouterInterface $router)
    {
        $this->currentDomaineProvider = $currentDomaineProvider;
        $this->router = $router;
    }

    public function getUrl()
    {
        $currentDomaine = $this->currentDomaineProvider->get();

        if ($currentDomaine && $cpArticle = $currentDomaine->getCommunautePratiqueArticle()) {
            return $this->router->generate('hopital_numerique_publication_publication_article', [
                'id' => $cpArticle->getId(),
                'categorie' => 'article',
                'alias' => $cpArticle->getAlias(),
            ]);
        }

        return $this->router->generate('hopital_numerique_homepage');
    }
}

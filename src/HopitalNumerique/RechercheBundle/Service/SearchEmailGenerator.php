<?php

namespace HopitalNumerique\RechercheBundle\Service;

use Nodevo\MailBundle\Entity\Mail;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;

/**
 * Class SearchEmailGenerator
 */
class SearchEmailGenerator
{
    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * @var string
     */
    protected $currentDomainUrl;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * SearchEmailGenerator constructor.
     *
     * @param MailManager     $mailManager
     * @param CurrentDomaine  $currentDomaine
     * @param RouterInterface $router
     */
    public function __construct(MailManager $mailManager, CurrentDomaine $currentDomaine, RouterInterface $router)
    {
        $this->mailManager = $mailManager;
        $this->currentDomainUrl = $currentDomaine->getUrl();
        $this->router = $router;
    }

    /**
     * Generates the email to share a saved search.
     *
     * @param Requete $search
     *
     * @return Mail
     */
    public function generateSearchEmail(Requete $search)
    {
        /** @var Mail $sendSearchEmail */
        $sendSearchEmail = clone $this->mailManager->findOneById(Mail::SHARE_SEARCH_EMAIL);

        if (null === $sendSearchEmail) {
            throw new NotFoundHttpException();
        }

        $resourceTypes = implode(array_map(function ($type) {
            return implode($type, ',');
        }, $search->getCategPointDur()), ',');

        $searchUrl = $this->currentDomainUrl
            . $this->router->generate('hopital_numerique_recherche_homepage_requete_generator', [
                'refs' => implode($search->getRefs(), ','),
                'q'    => $search->getRechercheTextuelle(),
                'type' => $resourceTypes,
            ])
        ;

        $variablesToReplace = [
            '%urlRecherche' => $searchUrl,
            '%nomRecherche' => $search->getNom(),
            '%nomUtilisateur' => $search->getUser()->getLastname(),
            '%prenomUtilisateur' => $search->getUser()->getFirstname(),
        ];

        foreach ($variablesToReplace as $key => $variable) {
            $sendSearchEmail->setBody(str_replace($key, nl2br($variable), $sendSearchEmail->getBody()));
        }

        return $sendSearchEmail;
    }
}

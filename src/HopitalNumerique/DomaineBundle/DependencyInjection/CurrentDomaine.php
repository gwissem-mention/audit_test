<?php
namespace HopitalNumerique\DomaineBundle\DependencyInjection;

use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Classe permettant de récupérer le domaine courant.
 */
class CurrentDomaine
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session
     */
    private $session;

    /**
     * @var \HopitalNumerique\DomaineBundle\Manager\DomaineManager DomaineManager
     */
    private $domaineManager;


    /**
     * Constructeur.
     */
    public function __construct(SessionInterface $session, DomaineManager $domaineManager)
    {
        $this->session = $session;
        $this->domaineManager = $domaineManager;
    }


    /**
     * Retourne le domaine courant.
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine Domaine
     */
    public function get()
    {
        $domaineId = $this->session->get('domaineId');
        $domaine = $this->domaineManager->findOneById($domaineId);

        if (null === $domaine) {
            throw new \Exception('Domaine introuvable.');
        }

        return $domaine;
    }

    /**
     * Retourne l'URL du domaine.
     *
     * @return string URL
     */
    public function getUrl()
    {
        $domaine = $this->get();

        return $domaine->getUrl();
    }
}

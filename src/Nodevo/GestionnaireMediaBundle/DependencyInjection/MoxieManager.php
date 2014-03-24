<?php
/**
 * Service gérant MoxieManager.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace Nodevo\GestionnaireMediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Service gérant MoxieManager.
 */
class MoxieManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     */
    private $container;

    /**
     * Constructeur du service MoxieManager.
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retourne la langue à utiliser pour MoxieManager en fonction de la langue utilisateur et des langues disponibles et par défaut sur MoxieManager.
     * 
     * @return string Le code langue de MoxieManager
     */
    public function getLangue()
    {
        $utilisateurLangue = $this->container->get('request')->getLocale();
        $moxieManagerLanguesDisponibles = $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.langue.langues_disponibles');
        
        if (in_array($utilisateurLangue, $moxieManagerLanguesDisponibles))
            return $utilisateurLangue;
        
        $moxieManagerLangueParDefaut = $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.langue.defaut');
        return $moxieManagerLangueParDefaut;
    }
}

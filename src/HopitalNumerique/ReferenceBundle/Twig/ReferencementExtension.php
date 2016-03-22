<?php
namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine as CurrentDomaineService;
use HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteReader;

/**
 * Extensions Twig concernant le référencement.
 */
class ReferencementExtension extends \Twig_Extension
{
    /**
     * @var \HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine CurrentDomaineService
     */
    private $currentDomaineService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteReader NoteReader
     */
    private $noteReader;


    /**
     * Constructeur.
     */
    public function __construct(CurrentDomaineService $currentDomaineService, NoteReader $noteReader)
    {
        $this->currentDomaineService = $currentDomaineService;
        $this->noteReader = $noteReader;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('referencement_note', array($this, 'getReferencementNote'))
        );
    }


    /**
     * Retourne la note d'un objet à afficher.
     *
     * @param object $entity Entité
     * @return string Note
     */
    public function getReferencementNote($entity)
    {
        $domaine = $this->currentDomaineService->get();
        return $this->noteReader->getNoteByEntityAndDomaineForAffichage($entity, $domaine);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reference_referencement_extension';
    }
}

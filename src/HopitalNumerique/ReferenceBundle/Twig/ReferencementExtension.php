<?php
namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine as CurrentDomaineService;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Doctrine\Referencement\NoteReader;
use HopitalNumerique\UserBundle\Entity\User;

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
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity Entity
     */
    private $entity;


    /**
     * Constructeur.
     */
    public function __construct(CurrentDomaineService $currentDomaineService, NoteReader $noteReader, Entity $entity)
    {
        $this->currentDomaineService = $currentDomaineService;
        $this->noteReader = $noteReader;
        $this->entity = $entity;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('referencement_note', array($this, 'getReferencementNote')),
            //@todo déplacer dans DomaineBundle
            new \Twig_SimpleFunction('domaines_communs_with_user', array($this, 'getDomainesCommunsWithUser'))
        );
    }


    /**
     * Retourne la note d'un objet à afficher.
     *
     * @param object $entity Entité
     * @return string Note
     */
    public function getReferencementNote($entity, Domaine $domaine = null)
    {
        if (null === $domaine) {
            $domaine = $this->currentDomaineService->get();
        }

        return $this->noteReader->getNoteByEntityAndDomaineForAffichage($entity, $domaine);
    }

    public function getDomainesCommunsWithUser($entity, User $user)
    {
        return $this->entity->getDomainesCommunsWithUser($entity, $user);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reference_referencement_extension';
    }
}

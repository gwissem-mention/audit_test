<?php

namespace HopitalNumerique\ReferenceBundle\Command;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update ambassadeur references.
 */
class UpdateAmbassadeurReferencesCommand extends ContainerAwareCommand
{
    private $profile = [
        283 => 283, // Administratif
        284 => 284, // Informatique
        285 => 285, // Médical ou Soignant
    ];

    private $typeEtablissement = [
        267 => 267, // Etablissement public
        268 => 268, // Etablissement privé
        269 => 269, // EBNL (à but non lucratif)
        307 => 307, // Groupe d'établissements
    ];

    private $activity = [
        272 => 272, // MCO
        273 => 273, // Psy
        274 => 274, // SSR
        275 => 275, // HAD
    ];

    private $role = [
        286 => 286, // Décideur
        287 => 287, // Chef de projet
        288 => 288, // Référent métier
    ];

    protected function configure()
    {
        $this
            ->setName('hn:reference:ambassadeur')
            ->setDescription('Met à jour les références des ambassadeurs en fonction de leurs données personnelles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initReferencesEntities();
        $container = $this->getContainer();

        $em = $container->get('doctrine.orm.entity_manager');

        $ambassadeurs = $container->get('hopitalnumerique_user.manager.user')->getAmbassadeurs();

        $persisted = [];
        foreach ($ambassadeurs as $ambassadeur) {
            $profileReference = $this->referenceProfile($ambassadeur);
            if (null !== $profileReference) {
                $em->persist($profileReference);
                $persisted[] = $profileReference;
            }

            $etablissementReference = $this->referenceEtablissement($ambassadeur);
            if (null !== $etablissementReference) {
                $em->persist($etablissementReference);
                $persisted[] = $etablissementReference;
            }

            $activityReferences = $this->referenceActivity($ambassadeur);
            foreach ($activityReferences as $activityReference) {
                if (null !== $activityReference) {
                    $em->persist($activityReference);
                    $persisted[] = $activityReference;
                }
            }

            $roleReference = $this->referenceRole($ambassadeur);
            if (null !== $roleReference) {
                $em->persist($roleReference);
                $persisted[] = $roleReference;
            }

            $em->flush();
            foreach ($persisted as $entity) {
                $em->detach($entity);
            }
        }

        $output->writeln('Done...');
    }

    private function referenceProfile(User $ambassadeur)
    {
        $container = $this->getContainer();
        $profileId = $ambassadeur->getProfileType()->getId();
        if (array_key_exists($profileId, $this->profile) && $this->profile[$profileId] instanceof Reference) {
            $profileReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                ->findOneBy([
                    'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
                    'entityId' => $ambassadeur->getId(),
                    'reference' => $this->profile[$profileId],
                ]);

            if (null === $profileReference) {
                $profileReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                    ->createEmpty();
                $profileReference->setEntityType(Entity::ENTITY_TYPE_AMBASSADEUR);
                $profileReference->setEntityId($ambassadeur->getId());
                $profileReference->setReference($this->profile[$profileId]);
                $profileReference->setPrimary(true);
            }

            return $profileReference;
        }

        return null;
    }

    private function referenceEtablissement(User $ambassadeur)
    {
        $container = $this->getContainer();
        $etablissement = $ambassadeur->getOrganization();
        if (!$etablissement instanceof Etablissement) {
            return null;
        }

        $type = $etablissement->getTypeOrganisme();
        if (!$type instanceof Reference) {
            return null;
        }

        $typeId = $type->getId();

        if (array_key_exists($typeId, $this->typeEtablissement)
            && $this->typeEtablissement[$typeId] instanceof Reference
        ) {
            $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                ->findOneBy([
                    'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
                    'entityId'   => $ambassadeur->getId(),
                    'reference'  => $this->typeEtablissement[$typeId],
                ])
            ;

            if (null === $etablissementReference) {
                $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                                                    ->createEmpty();
                $etablissementReference->setEntityType(Entity::ENTITY_TYPE_AMBASSADEUR);
                $etablissementReference->setEntityId($ambassadeur->getId());
                $etablissementReference->setReference($this->typeEtablissement[$typeId]);
                $etablissementReference->setPrimary(true);
            }

            return $etablissementReference;
        }

        return null;
    }

    /**
     * @param User $ambassadeur
     *
     * @return array
     */
    private function referenceActivity(User $ambassadeur)
    {
        $container = $this->getContainer();
        $activities = $ambassadeur->getActivities();
        $references = [];

        foreach ($activities as $activity) {
            $activityId = $activity->getId();
            if (array_key_exists($activityId, $this->activity) && $this->activity[$activityId] instanceof Reference) {
                $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                    ->findOneBy([
                        'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
                        'entityId' => $ambassadeur->getId(),
                        'reference' => $this->activity[$activityId],
                    ]);

                if (null === $etablissementReference) {
                    $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                        ->createEmpty();
                    $etablissementReference->setEntityType(Entity::ENTITY_TYPE_AMBASSADEUR);
                    $etablissementReference->setEntityId($ambassadeur->getId());
                    $etablissementReference->setReference($this->activity[$activityId]);
                    $etablissementReference->setPrimary(true);
                }
                $references[] = $etablissementReference;
            }
        }

        return $references;
    }

    private function referenceRole(User $ambassadeur)
    {
        $container = $this->getContainer();
        $role = $ambassadeur->getJobType();
        if (!$role instanceof Reference) {
            return null;
        }

        $roleId = $role->getId();

        if (array_key_exists($roleId, $this->role) && $this->role[$roleId] instanceof Reference) {
            $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                ->findOneBy([
                    'entityType' => Entity::ENTITY_TYPE_AMBASSADEUR,
                    'entityId' => $ambassadeur->getId(),
                    'reference' => $this->role[$roleId],
                ]);

            if (null === $etablissementReference) {
                $etablissementReference = $container->get('hopitalnumerique_reference.manager.entity_has_reference')
                    ->createEmpty();
                $etablissementReference->setEntityType(Entity::ENTITY_TYPE_AMBASSADEUR);
                $etablissementReference->setEntityId($ambassadeur->getId());
                $etablissementReference->setReference($this->role[$roleId]);
                $etablissementReference->setPrimary(true);
            }

            return $etablissementReference;
        }

        return null;
    }

    private function initReferencesEntities()
    {
        foreach ($this->profile as &$reference) {
            $reference = $this->getContainer()->get('hopitalnumerique_reference.manager.reference')
                ->findOneById($reference);
        }

        foreach ($this->typeEtablissement as &$reference) {
            $reference = $this->getContainer()->get('hopitalnumerique_reference.manager.reference')
                ->findOneById($reference);
        }

        foreach ($this->activity as &$reference) {
            $reference = $this->getContainer()->get('hopitalnumerique_reference.manager.reference')
                ->findOneById($reference);
        }

        foreach ($this->role as &$reference) {
            $reference = $this->getContainer()->get('hopitalnumerique_reference.manager.reference')
                ->findOneById($reference);
        }
    }
}

<?php

namespace HopitalNumerique\CoreBundle\Command;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigrateRelationsCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('app:core:migrate:relations')
            ->setDescription('Migrate relations')
            ->setHelp('Migrate relations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $relationRepository = $this->getContainer()->get(RelationRepository::class);
        $objectRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.objet');
        $relatedBoardsRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.related_board');
        $contentRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.contenu');

        /** @var Contenu $content */
        foreach ($contentRepository->findAll() as $content) {
            if (null === $content->getObjets() || 0 === $content->getObjets()->count()) {
                continue;
            }

            foreach ($content->getObjets() as $linkedObject) {
                if (false !== strpos($linkedObject, 'INFRADOC')) {
                    $class = Contenu::class;
                } else {
                    $class = Objet::class;
                }

                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($content), new ObjectIdentity($class, explode(':', $linkedObject)[1]));
            }

            $entityManager->flush();
        }

        /** @var Objet $object */
        foreach ($objectRepository->findAll() as $object) {
            foreach ($relatedBoardsRepository->findByObject($object) as $board) {
                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), ObjectIdentity::createFromDomainObject($board->getBoard()));
            }

            foreach ($object->getRelatedRisks() as $risk) {
                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), ObjectIdentity::createFromDomainObject($risk->getRisk()));
            }

            foreach ($object->getObjets() as $linkedObject) {
                if (false !== strpos($linkedObject, 'INFRADOC')) {
                    $class = Contenu::class;
                } else {
                    $class = Objet::class;
                }

                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), new ObjectIdentity($class, explode(':', $linkedObject)[1]));
            }

            $entityManager->flush();
        }

    }
}

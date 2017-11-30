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
        $output->writeln('*************************');
        $output->writeln('*** Migrate relations ***');
        $output->writeln('*************************');
        $output->writeln('');


        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $relationRepository = $this->getContainer()->get(RelationRepository::class);
        $objectRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.objet');
        $relatedBoardsRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.related_board');
        $contentRepository = $this->getContainer()->get('hopitalnumerique_objet.repository.contenu');


        $output->writeln('> Content');
        /** @var Contenu $content */
        foreach ($contentRepository->findAll() as $content) {
            if (null === $content->getObjets() || 0 === $content->getObjets()->count()) {
                continue;
            }

            $output->write(sprintf('    %s', $content->getTitre()));

            foreach ($content->getObjets() as $linkedObject) {
                if (false !== strpos($linkedObject, 'INFRADOC')) {
                    $class = Contenu::class;
                } else {
                    $class = Objet::class;
                }

                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($content), new ObjectIdentity($class, explode(':', $linkedObject)[1]));

                $output->write('.');
            }

            $entityManager->flush();

            $output->writeln(' [SAVED]');
        }

        $output->writeln('');
        $output->writeln('> Objects');
        /** @var Objet $object */
        foreach ($objectRepository->findAll() as $object) {
            $relationAdded = false;
            $output->write(sprintf('    %s', $object->getTitre()));

            foreach ($relatedBoardsRepository->findByObject($object) as $board) {
                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), ObjectIdentity::createFromDomainObject($board->getBoard()));
                $output->write('.');
                $relationAdded = true;
            }

            foreach ($object->getRelatedRisks() as $risk) {
                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), ObjectIdentity::createFromDomainObject($risk->getRisk()));
                $output->write('.');
                $relationAdded = true;
            }

            foreach ($object->getObjets() as $linkedObject) {
                if (false !== strpos($linkedObject, 'INFRADOC')) {
                    $class = Contenu::class;
                } else {
                    $class = Objet::class;
                }

                $relationRepository->addRelation(ObjectIdentity::createFromDomainObject($object), new ObjectIdentity($class, explode(':', $linkedObject)[1]));
                $output->write('.');
                $relationAdded = true;
            }

            if ($relationAdded) {
                $entityManager->flush();

                $output->writeln(' [SAVED]');
            } else {
                $output->writeln(' [SKIP]');
            }

        }

        $output->writeln('');
        $output->writeln('');

    }
}

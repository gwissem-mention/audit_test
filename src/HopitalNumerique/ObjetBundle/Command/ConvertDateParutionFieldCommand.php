<?php

namespace HopitalNumerique\ObjetBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class ConvertDateParutionFieldCommand
 *
 * This class have to be removed after dateModification to releaseDate migration
 * and should not be used for anything else.
 */
class ConvertDateParutionFieldCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hn:convert:date-parution')
            ->setDescription('Converts all dateParution string value to DateTime.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Conversion started.');

        $months = [
            'Janvier' => '01',
            'janvier' => '01',
            'Février' => '02',
            'février' => '02',
            'Fevrier' => '02',
            'fevrier' => '02',
            'Mars' => '03',
            'mars' => '03',
            'Avril' => '04',
            'avril' => '04',
            'Mai' => '05',
            'mai' => '05',
            'Juin' => '06',
            'juin' => '06',
            'Juillet' => '07',
            'juillet' => '07',
            'Août' => '08',
            'août' => '08',
            'Aout' => '08',
            'aout' => '08',
            'Septembre' => '09',
            'septembre' => '09',
            'Octobre' => '10',
            'octobre' => '10',
            'Novembre' => '11',
            'novembre' => '11',
            'nov' => '11',
            'Décembre' => '12',
            'décembre' => '12',
            'Decembre' => '12',
            'decembre' => '12',
        ];

        $intValues = [
            '1' => '01',
            '2' => '02',
            '3' => '03',
            '4' => '04',
            '5' => '05',
            '6' => '06',
            '7' => '07',
            '8' => '08',
            '9' => '09',
        ];

        $objects = $this->getContainer()->get('hopitalnumerique_objet.repository.objet')->findByDateParutionNotNull();

        foreach ($objects as $object) {
            $objectDate = explode(' ', $object->getDateParution());

            if (count($objectDate) === 1) {
                $objectDate = explode('/', $object->getDateParution());
            }

            foreach ($objectDate as &$item) {
                if ("" === $item && ($key = array_search($item, $objectDate))) {
                    unset($objectDate[$key]);
                    continue 2;
                }

                if (isset($months[$item])) {
                    $item = $months[$item];
                }

                if (isset($intValues[$item])) {
                    $item = $intValues[$item];
                }
            }

            if (count($objectDate) === 1) {
                if (strlen($objectDate[0]) === 4) {
                    $objectDate = ['01', '01', $objectDate[0]];
                } elseif (strlen($objectDate[0]) === 2) {
                    $objectDate = ['01', $objectDate[0], '2017'];
                } else {
                    $objectDate = [];
                }
            }

            if (count($objectDate) === 2) {
                $objectDate = ['01', $objectDate[0], $objectDate[1]];
            }

            if (count($objectDate) === 3) {
                if (strlen($objectDate[2]) === 2) {
                    $objectDate[2] = '20' . $objectDate[2];
                }
            }

            $objectDate = "" === implode('/', $objectDate) ? null : implode('/', $objectDate);

            $object->setReleaseDate(new \DateTime($objectDate));

            if (null === $objectDate) {
                $output->writeln('ERROR: ' . $object->getDateParution() . ' cannot be converted.');
            } else {
                $output->writeln($object->getDateParution() . ' converted to ' . $objectDate . '.');
            }
        }

        try {
            $this->getContainer()->get('doctrine.orm.entity_manager')->flush($objects);

            $output->writeln('Done.');
        } catch (\Exception $exception) {
            $output->writeln('An error occurred.');
        }
    }
}

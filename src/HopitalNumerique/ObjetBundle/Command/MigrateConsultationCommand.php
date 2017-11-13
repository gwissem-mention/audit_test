<?php

namespace HopitalNumerique\ObjetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateConsultationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hn:object:consultation:migrate')
            ->setDescription('Migrate consultation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('**********************************');
        $output->writeln('*** Starting \'views\' migration ***');
        $output->writeln('**********************************');

        $container = $this->getContainer();
        $db = $container->get('database_connection');

        $insertQuery = 'INSERT INTO hn_objet_consultation (usr_id, obj_session_id, obj_id, cln_date_last_consulted, con_id, dom_id, viewsCount) VALUES %s';
        $queries = [];

        $bulk = 100;
        $i = 0;
        foreach ($db->query('SELECT * FROM hn_objet_consultation') as $view) {

            for ($j = 1; $j <= $view['viewsCount']; $j++) {
                $i++;
                $queries[] =
                    sprintf(
                        '(%s, %s, %s, "%s", %s, %s, -1)',
                        $view['usr_id'] ?: "null",
                        $view['obj_session_id'] ? '"'.$view['obj_session_id'].'"' : "null",
                        $view['obj_id'],
                        $view['cln_date_last_consulted'],
                        $view['con_id'] ?: "null",
                        $view['dom_id'] ?: "null"
                    )
                ;

                $output->write('.');

                if ($i >= $bulk) {
                    $output->write('-');
                    $db->query(sprintf($insertQuery, implode(', ', $queries)));
                    $output->write('!');

                    $i = 0;
                    $queries = [];
                }
            }
        }

        $db->query(sprintf($insertQuery, implode(', ', $queries)));
        $db->query('DELETE FROM hn_objet_consultation WHERE viewsCount >= 0');
    }
}

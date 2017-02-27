<?php

namespace Nodevo\ToolsBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Nodevo\ToolsBundle\Generator\EntityGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\DBAL\Types\Type;

class EntityCommand extends GenerateDoctrineEntityCommand
{
    protected $generator;

    protected function configure()
    {
        parent::configure();

        $this->setName('nodevo:generate:entity');
        $this->setDescription('Création des entités nodevo');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Doctrine2 entity generator');

        // namespace
        $output->writeln([
            '',
            'This command helps you generate Doctrine2 entities.',
            '',
            'First, you need to give the entity name you want to generate.',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ]);

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), ['Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'], false, $input->getOption('entity'), $bundleNames);

            list($bundle, $entity) = $this->parseShortcutNotation($entity);

            // check reserved words
            if ($this->getGenerator()->isReservedKeyword($entity)) {
                $output->writeln(sprintf('<bg=red> "%s" is a reserved word</>.', $entity));
                continue;
            }

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath() . '/Entity/' . str_replace('\\', '/', $entity) . '.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Entity "%s:%s" already exists</>.', $bundle, $entity));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('entity', $bundle . ':' . $entity);

        // format
        $output->writeln([
            '',
            'Determine the format to use for the mapping information.',
            '',
        ]);

        $formats = ['yml', 'xml', 'php', 'annotation'];

        $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), ['Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'], false, $input->getOption('format'), $formats);
        $input->setOption('format', $format);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $dialog));

        // repository?
        $input->setOption('with-repository', true);

        // summary
        $output->writeln([
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf('You are going to generate a "<info>%s:%s</info>" Doctrine2 entity', $bundle, $entity),
            sprintf('using the "<info>%s</info>" format.', $format),
            '',
        ]);
    }

    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = [];
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;

                $fields[$name] = ['fieldName' => $name, 'type' => $type, 'length' => $length];
            }
        }

        return $fields;
    }

    private function addFields(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln([
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ]);
        $output->write('<info>Available types:</info> ');

        $types = array_keys(Type::getTypesMap());
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        $lengthValidator = function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getGenerator();
            $columnName = $dialog->askAndValidate($output, $dialog->getQuestion('New field name (press <return> to stop adding fields)', null), function ($name) use ($fields, $generator) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                }

                // check reserved words
                if ($generator->isReservedKeyword($name)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                }

                return $name;
            });
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            // try to guess the type by the column name prefix/suffix
            if (substr($columnName, -3) == '_at') {
                $defaultType = 'datetime';
            } elseif (substr($columnName, -3) == '_id') {
                $defaultType = 'integer';
            } elseif (substr($columnName, 0, 3) == 'is_') {
                $defaultType = 'boolean';
            } elseif (substr($columnName, 0, 4) == 'has_') {
                $defaultType = 'boolean';
            }

            $type = $dialog->askAndValidate($output, $dialog->getQuestion('Field type', $defaultType), $fieldValidator, false, $defaultType, $types);

            $data = ['columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type];

            if ($type == 'string') {
                $data['length'] = $dialog->askAndValidate($output, $dialog->getQuestion('Field length', 255), $lengthValidator, false, 255);
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    /**
     * Ajoute le chemin du skeleton de notre ToolsBundle.
     */
    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            $path = $this->getContainer()->get('kernel')->locateResource('@NodevoToolsBundle/Resources/skeleton/');

            $this->generator = $this->createGenerator();
            $skeletonDirs = array_merge([$path], $this->getSkeletonDirs($bundle));

            $this->generator->setSkeletonDirs($skeletonDirs);
        }

        return $this->generator;
    }

    /**
     * Créer un generator de type Nodevo.
     *
     * @return EntityGenerator
     */
    protected function createGenerator()
    {
        return new EntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }
}

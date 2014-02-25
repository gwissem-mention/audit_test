<?php

namespace Nodevo\MailBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NodevoMailExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('nodevo_mail.mail.allowAdd', $config['autorisations']['allowAdd']);
        $container->setParameter('nodevo_mail.mail.allowDelete', $config['autorisations']['allowDelete']);
        if (isset($config['expediteur']))
        {
            $container->setParameter('nodevo_mail.manager.mail.nomExpediteur', $config['expediteur']['nom']);
            $container->setParameter('nodevo_mail.manager.mail.emailExpediteur', $config['expediteur']['mail']);
        }
        if (isset($config['test']['destinataire']))
            $container->setParameter('nodevo_mail.manager.mail.destinataire', $config['test']['destinataire']);
    }
}

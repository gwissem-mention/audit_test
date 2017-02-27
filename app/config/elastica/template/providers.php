<?php

use Symfony\Component\DependencyInjection\Definition;
use HopitalNumerique\SearchBundle\Service\Provider\AutodiagProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ObjectProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ContentProvider;
use HopitalNumerique\SearchBundle\Service\Provider\GroupProvider;
use HopitalNumerique\SearchBundle\Service\Provider\UserProvider;
use Symfony\Component\DependencyInjection\Reference;

$container
    ->setDefinition("hopital_numerique_search.provider.autodiag.$serviceIdentifier", new Definition(
        AutodiagProvider::class,
        [
            $slug,
            new Reference('autodiag.repository.attribute'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'autodiag')),
            new Reference('fos_elastica.index_manager'),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'autodiag',
    ])
;

$container
    ->setDefinition("hopital_numerique_search.provider.object.$serviceIdentifier", new Definition(
        ObjectProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_objet.repository.objet'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'object')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'object',
    ])
;

$container
    ->setDefinition("hopital_numerique_search.provider.content.$serviceIdentifier", new Definition(
        ContentProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_objet.repository.contenu'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'content')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'content',
    ])
;

$container
    ->setDefinition("hopital_numerique_search.provider.group.$serviceIdentifier", new Definition(
        GroupProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_communautepratique.repository.group'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'cdp_groups')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'cdp_groups',
    ])
;

$container
    ->setDefinition("hopital_numerique_search.provider.person.$serviceIdentifier", new Definition(
        UserProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_user.repository.user'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'person')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'person',
    ])
;

<?php

use Symfony\Component\DependencyInjection\Definition;
use HopitalNumerique\SearchBundle\Service\Provider\AutodiagProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ObjectProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ContentProvider;
use HopitalNumerique\SearchBundle\Service\Provider\GroupProvider;
use HopitalNumerique\SearchBundle\Service\Provider\UserProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ForumPostProvider;
use HopitalNumerique\SearchBundle\Service\Provider\ForumTopicProvider;
use Symfony\Component\DependencyInjection\Reference;

$container
    ->setDefinition("hopital_numerique_search.provider.autodiag.$serviceIdentifier", new Definition(
        AutodiagProvider::class,
        [
            $slug,
            new Reference('autodiag.repository.autodiag'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'autodiag')),
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
    ->setDefinition("hopital_numerique_search.provider.cdp_discussion.$serviceIdentifier", new Definition(
        \HopitalNumerique\SearchBundle\Service\Provider\CDPDiscussionProvider::class,
        [
            $slug,
            new Reference('HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'cdp_discussion')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'cdp_discussion',
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

$container
    ->setDefinition("hopital_numerique_search.provider.forum_topic.$serviceIdentifier", new Definition(
        ForumTopicProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_forum.repository.topic'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'forum_topic')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'forum_topic',
    ])
;

$container
    ->setDefinition("hopital_numerique_search.provider.forum_post.$serviceIdentifier", new Definition(
        ForumPostProvider::class,
        [
            $slug,
            new Reference('hopitalnumerique_forum.repository.post'),
            new Reference(sprintf('fos_elastica.object_persister.%s.%s', $index, 'forum_post')),
        ]
    ))
    ->addTag('fos_elastica.provider', [
        'index' => $index,
        'type' => 'forum_post',
    ])
;

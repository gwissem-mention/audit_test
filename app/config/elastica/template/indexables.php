<?php

use Symfony\Component\DependencyInjection\Definition;
use HopitalNumerique\SearchBundle\Service\Indexable\UserIndexable;
use HopitalNumerique\SearchBundle\Service\Indexable\GroupIndexable;
use HopitalNumerique\SearchBundle\Service\Indexable\PublicationIndexable;

$container->setDefinition("hopital_numerique_search.indexable.user.$serviceIdentifier", new Definition(
    UserIndexable::class,
    [
        $slug
    ]
));

$container->setDefinition("hopital_numerique_search.indexable.groupe.$serviceIdentifier", new Definition(
    GroupIndexable::class,
    [
        $slug
    ]
));

$container->setDefinition("hopital_numerique_search.indexable.publication.$serviceIdentifier", new Definition(
    PublicationIndexable::class,
    [
        $slug
    ]
));

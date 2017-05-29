<?php

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

require 'template/indexables.php';
require 'template/providers.php';

$container->loadFromExtension('fos_elastica', [
    'indexes' => [
        $index => [
            'settings' => [
                'index' => [
                    'analysis' => [
                        'analyzer' => [
                            'content_analyzer' => [
                                'type' => 'custom',
                                'char_filter' => ['html_strip'],
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding', 'stop_filter', 'my_stemmer'],
                            ],
                            'title_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding', 'stop_filter', 'my_stemmer', 'synonym_domaine'],
                            ],
                            'name_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding', 'name_ngram'],
                            ],
                        ],
                        'filter' => [
                            'stop_filter' => [
                                'type' => 'stop',
                                'stopwords' => '_french_',
                            ],
                            'my_stemmer' => [
                                'type' => 'snowball',
                                'name' => 'French',
                            ],
                            'my_ngram' => [
                                'type' => 'nGram',
                                'min_gram' => 3,
                                'max_gram' => 10,
                            ],
                            'name_ngram' => [
                                'type' => 'edgeNGram',
                                'min_gram' => 2,
                                'max_gram' => 10,
                            ],
                            'synonym_domaine' => [
                                'type' => 'synonym',
                                'synonyms' => ['automobile , voiture => urbain , citadine'],
                            ]
                        ],
                    ],
                ],
            ],
            'types' => [
                'object' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'synthese' => null,
                        'domaines' => [
                            'type' => 'nested',
                            'properties' => [
                                'nom' => null,
                            ],
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'resume',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'alias' => [
                            'type' => 'keyword',
                        ],
                        'types' => [
                            'type' => 'nested',
                            'properties' => [
                                'libelle' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                        'restricted_roles' => [
                            'property_path' => 'roles',
                            'index' => 'not_analyzed',
                        ]
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Objet::class,
                        'provider' => [
                            'service' => "@hopital_numerique_search.provider.object.$serviceIdentifier",
                        ],
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.publication.$serviceIdentifier",
                        'isObjectIndexable',
                    ],
                ],
                'content' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'contenu',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'alias' => [
                            'type' => 'keyword',
                        ],
                        'types' => [
                            'type' => 'nested',
                            'property_path' => 'objet.types',
                            'properties' => [
                                'libelle' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                        'parent' => [
                            'type' => 'nested',
                            'property_path' => 'objet',
                            'properties' => [
                                'title' => [
                                    'type' => 'text',
                                    'property_path' => 'titre',
                                    'term_vector' => 'with_positions_offsets',
                                ],
                                'id' => null,
                                'alias' => null,
                                'types' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'libelle' => [
                                            'type' => 'keyword',
                                        ],
                                    ],
                                ],
                                'restricted_roles' => [
                                    'property_path' => 'roles',
                                    'index' => 'not_analyzed',
                                ],
                            ],
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Contenu::class,
                        'provider' => [
                            'service' => "@hopital_numerique_search.provider.content.$serviceIdentifier",
                        ],
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.publication.$serviceIdentifier",
                        'isContentIndexable',
                    ],
                ],
                'forum_post' => [
                    'mappings' => [
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'body',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'topic' => [
                            'type' => 'nested',
                            'properties' => [
                                'id' => null,
                                'title' => null,
                                'forumName' => [
                                    'property_path' => 'board.category.forum.name',
                                ],
                            ],
                        ],
                        'authorised_roles' => [
                            'property_path' => 'topic.board.category.forum.readAuthorisedRoles',
                            'index' => 'not_analyzed',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Post::class,
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.forum.$serviceIdentifier",
                        'isPostIndexable',
                    ],
                ],
                'forum_topic' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'forumName' => [
                            'property_path' => 'board.category.forum.name',
                        ],
                        'authorised_roles' => [
                            'property_path' => 'board.category.forum.readAuthorisedRoles',
                            'index' => 'not_analyzed',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Topic::class,
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.forum.$serviceIdentifier",
                        'isTopicIndexable',
                    ],
                ],
                'cdp_groups' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                            'term_vector' => 'with_positions_offsets',
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'descriptionCourte',
                            'term_vector' => 'with_positions_offsets',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Groupe::class,
                        'provider' => [
                            'service' => "hopital_numerique_search.provider.group.$serviceIdentifier",
                        ]
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.groupe.$serviceIdentifier",
                        'isIndexable',
                    ],
                ],
                'person' => [
                    'mappings' => [
                        'username' => [
                            'analyzer' => 'name_analyzer',
                        ],
                        'firstname' => [
                            'property_path' => 'prenom',
                            'analyzer' => 'name_analyzer',
                        ],
                        'lastname' => [
                            'property_path' => 'nom',
                            'analyzer' => 'name_analyzer',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => User::class,
                        'provider' => [
                            'service' => "@hopital_numerique_search.provider.person.$serviceIdentifier",
                        ],
                    ],
                    'indexable_callback' => [
                        "@hopital_numerique_search.indexable.user.$serviceIdentifier",
                        'isIndexable',
                    ],
                ],
                'autodiag' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'term_vector' => 'with_positions_offsets',
                        ],
                    ],
                    'persistence' => [
                        'model' => Chapter::class,
                        'listener' => [
                            'insert' => false,
                            'update' => false,
                            'delete' => false,
                        ],
                        'provider' => [
                            'service' => "@hopital_numerique_search.provider.autodiag.$serviceIdentifier",
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

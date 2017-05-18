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
                                'filter' => ['lowercase', 'stop_filter', 'my_stemmer'],
                            ],
                            'title_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'stop_filter', 'my_stemmer', 'synonym_domaine'],
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
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                        ],
                        'synthese' => null,
                        'domaines' => [
                            'type' => 'nested',
                            'properties' => [
                                'nom' => null,
                            ],
                        ],
                        'content' => [
                            'type' => 'string',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'resume',
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
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                        ],
                        'content' => [
                            'type' => 'string',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'contenu',
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
                                    'property_path' => 'titre',
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
                            'type' => 'string',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'body',
                        ],
                        'topic' => [
                            'type' => 'nested',
                            'properties' => [
                                'id' => null,
                                'title' => null,
                                'forumName' => [
                                    'property_path' => 'board.category.forumName',
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
                ],
                'forum_topic' => [
                    'mappings' => [
                        'title' => [
                            'analyzer' => 'title_analyzer',
                        ],
                        'forumName' => [
                            'property_path' => 'board.category.forumName',
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
                ],
                'cdp_groups' => [
                    'mappings' => [
                        'title' => [
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                        ],
                        'descriptionCourte' => null,
                        'descriptionHtml' => null,
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
                            'analyzer' => 'title_analyzer',
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

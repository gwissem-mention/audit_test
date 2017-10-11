<?php

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
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
                                'filter' => [
                                    'lowercase',
                                    'stop_filter',
                                    'elision',
                                    'asciifolding',
                                    'word_delimiter',
                                    'my_stemmer',
                                ],
                            ],
                            'content_exact_analyzer' => [
                                'type' => 'custom',
                                'char_filter' => ['html_strip'],
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'stop_filter',
                                    'elision',
                                    'asciifolding',
                                ],
                            ],
                            'title_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'stop_filter',
                                    'elision',
                                    'asciifolding',
                                    'word_delimiter',
                                    'my_stemmer',
                                    'synonym_domaine',
                                ],
                            ],
                            'title_exact_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'stop_filter',
                                    'elision',
                                    'asciifolding',
                                ],
                            ],
                            'name_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding'],
                            ],
                            'phone_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'keyword',
                                'filter' => ['phone_delimiter']
                            ]
                        ],
                        'filter' => [
                            'stop_filter' => [
                                'type' => 'stop',
                                'stopwords' => '_french_',
                            ],
                            'my_stemmer' => [
                                'type' => 'snowball',
                                'name' => 'french',
                            ],
                            'minimal_stemmer' => [
                                'type' => 'stemmer',
                                'name' => 'minimal_french',
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
                            ],
                            'elision' => [
                                'type' => 'elision',
                                'articles' => ['l', 'm', 't', 'qu', 'n', 's', 'j', 'd'],
                            ],
                            'word_delimiter' => [
                                'type' => 'word_delimiter',
                            ],
                            'phone_delimiter' => [
                                'type' => 'pattern_replace',
                                'pattern' => '\ ',
                                'replacement' => ''
                            ],
                        ],
                    ],
                ],
            ],
            'types' => [
                'cdp_message' => [
                    'mappings' => [
                        'discussionId' => [
                            'type' => 'keyword',
                            'property_path' => 'discussion.id',
                        ],
                        'title' => [
                            'type' => 'text',
                            'property_path' => 'discussion.title',
                            'analyzer' => 'title_analyzer',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'domaines' => [
                            'property_path' => 'discussion.domains',
                            'type' => 'nested',
                            'properties' => [
                                'nom' => null,
                            ],
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Message::class,
                        'provider' => [
                            'service' => "hopital_numerique_search.provider.cdp_message.$serviceIdentifier",
                        ],
                    ],
                ],
                'object' => [
                    'mappings' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titre',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'synthesis' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'synthese',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                ],
                            ],
                        ],
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'alias' => [
                            'type' => 'keyword',
                        ],
                        'source' => [
                            'type' => 'text',
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'title_tree' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'property_path' => 'titleTree',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                ],
                            ],
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'contenu',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'content_code' => [
                            'type' => 'keyword',
                            'property_path' => 'prefix',
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
                                'source' => [
                                    'type' => 'text',
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
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
                            'type' => 'keyword',
                            'property_path' => false,
                            'index' => 'not_analyzed',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Post::class,
                        'provider' => [
                            'service' => "hopital_numerique_search.provider.forum_post.$serviceIdentifier",
                        ],
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                        'content' => [
                            'type' => 'text',
                            'property_path' => 'firstPostBody',
                            'analyzer' => 'content_analyzer',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                        'forumName' => [
                            'property_path' => 'board.category.forum.name',
                            "analyzer" => "title_analyzer",
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                        'authorised_roles' => [
                            'type' => 'keyword',
                            'property_path' => false,
                            'index' => 'not_analyzed',
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Topic::class,
                        'provider' => [
                            'service' => "hopital_numerique_search.provider.forum_topic.$serviceIdentifier",
                        ],
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'descriptionCourte',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'content_analyzer',
                            'property_path' => 'descriptionHtml',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ]
                            ]
                        ],
                    ],
                    'persistence' => [
                        'driver' => 'orm',
                        'model' => Groupe::class,
                        'provider' => [
                            'service' => "hopital_numerique_search.provider.group.$serviceIdentifier",
                        ],
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
                            'property_path' => 'firstname',
                            'analyzer' => 'name_analyzer',
                        ],
                        'lastname' => [
                            'property_path' => 'lastname',
                            'analyzer' => 'name_analyzer',
                        ],
                        'biography' => [
                            'property_path' => 'presentation',
                            'analyzer' => 'content_analyzer',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "content_exact_analyzer",
                                ],
                            ],
                        ],
                        'phone' => [
                            'property_path' => 'phoneNumber',
                            'analyzer' => 'phone_analyzer',
                        ],
                        'cellphone' => [
                            'property_path' => 'cellPhoneNumber',
                            'analyzer' => 'phone_analyzer',
                        ],
                        'email' => [
                            'index' => 'not_analyzed',
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
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'chapter_label' => [
                            'type' => 'text',
                            'analyzer' => 'title_analyzer',
                            'term_vector' => 'with_positions_offsets',
                            'fields' => [
                                'exact' => [
                                    "type" => "text",
                                    "analyzer" => "title_exact_analyzer",
                                    'term_vector' => 'with_positions_offsets',
                                ],
                            ],
                        ],
                        'chapter_code' => [
                            'type' => 'keyword',
                            'property_path' => false,
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

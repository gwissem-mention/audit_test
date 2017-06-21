<?php

$domaines = [
    'numerique',
    'macrodiag',
    'transports-sanitaires',
    'psychiatrie-sante-mentale',
    'formations-plan-triennal',
    'rse',
    'bloc-operatoire',
    'pf-test',
    'medicaments',
    'gestion-des-lits',
    'immobilier',
    'imagerie',
    'urgences',
    'si-ght',
    'ressources',
    'consultation-secretariat',
];

foreach ($domaines as $slug) {
    $serviceIdentifier = str_replace('-', '', $slug);
    $index = sprintf("%s_%s", $container->getParameter('elastica_index_prefix'), str_replace('-', '_', $slug));

    require "template.php";
}

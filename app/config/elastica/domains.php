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
];

foreach ($domaines as $slug) {
    $serviceIdentifier = str_replace('-', '', $slug);
    $index = sprintf("cdr_domaine_%s", str_replace('-', '_', $slug));

    require "template.php";
}

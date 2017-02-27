<?php

$domaines = [
    'mon-hopital-numerique',
    'macrodiagnostic',
];

foreach ($domaines as $slug) {
    $serviceIdentifier = str_replace('-', '', $slug);
    $index = sprintf("cdr_domaine_%s", str_replace('-', '_', $slug));

    require "template.php";
}

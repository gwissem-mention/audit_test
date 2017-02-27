<?php

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Rpodwika\Silex\YamlConfigServiceProvider("../../app/config/parameters.yml"));
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

/* Services declaration */
$app['query.transformer'] = function () {
    return new \Search\Service\RequestToQueryTransformer();
};

$app['query.factory'] = function () {
    return new \Search\Service\ElasticaQueryFactory();
};

$app['elastic.client'] = function () use ($app) {
    return new Elastica\Client(array(
        'host' => $app['config']['parameters']['elastica_host'],
        'port' => $app['config']['parameters']['elastica_port'],
    ));
};

$app['search.repository'] = function () use ($app) {
    return new \Search\Service\SearchRepository($app['query.factory'], $app['elastic.client']);
};

$app['search.controller'] = function () use ($app) {
    return new \Search\Controller\SearchController($app['search.repository'], $app['query.transformer']);
};
/************************/


$app['debug'] = true;

/* Routes declaration */
$app->get('/', "search.controller:indexAction");
$app->get('/hot', "search.controller:hotAction");
/**********************/

/* Global middleware */
$app->after(function (Request $request, Response $response) {
    // Set access control allow origin on all requests
    $response->headers->add([
        'Access-Control-Allow-Origin' => '*',
    ]);
});

$app->run();

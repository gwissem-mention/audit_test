<?php

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Rpodwika\Silex\YamlConfigServiceProvider("../../app/config/parameters.yml"));
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

/* Services declaration */
$app['db'] = function () use ($app) {
    $host = $app['config']['parameters']['database_host'];
    $database = $app['config']['parameters']['database_name'];
    $user = $app['config']['parameters']['database_user'];
    $password = $app['config']['parameters']['database_password'];
    return new \PDO(
        "mysql:host=$host;dbname=$database",
        $user,
        $password,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
    );
};

$app['user.repository'] = function () use ($app) {
    return new \Search\Service\UserRepository($app['db']);
};

$app['query.transformer'] = function () {
    return new \Search\Service\RequestToQueryTransformer();
};

$app['query.factory'] = function () use ($app) {
    $config = new \Search\Service\QueryConfigurator;
    $factory = new \Search\Service\ElasticaQueryFactory($config);
    $factory->addTypeFactory(new \Search\Service\TypeFactory\ObjectTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\ContentTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\AutodiagTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\PersonTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\PostTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\TopicTypeFactory($config));
    $factory->addTypeFactory(new \Search\Service\TypeFactory\GroupTypeFactory($config));

    return $factory;
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

$app['stats.repository'] = function () use ($app) {
    return new \Search\Service\SearchStatsRepository($app['db']);
};

$app['search.controller'] = function () use ($app) {
    return new \Search\Controller\SearchController($app['search.repository'], $app['user.repository'], $app['query.transformer'], $app['stats.repository']);
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

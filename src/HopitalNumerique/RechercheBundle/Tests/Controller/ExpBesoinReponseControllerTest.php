<?php

namespace HopitalNumerique\RechercheBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExpBesoinReponseControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/add');
    }

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/delete');
    }

    public function testEdit()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/edit');
    }

    public function testSave()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/save');
    }

    public function testReorder()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/reorder');
    }

}

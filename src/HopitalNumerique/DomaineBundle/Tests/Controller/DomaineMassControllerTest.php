<?php

namespace HopitalNumerique\DomaineBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DomaineMassControllerTest extends WebTestCase
{
    public function testDeletemass()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/deleteMass');
    }

}

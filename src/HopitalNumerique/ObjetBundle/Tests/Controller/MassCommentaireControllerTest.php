<?php

namespace HopitalNumerique\ObjetBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MassCommentaireControllerTest extends WebTestCase
{
    public function testExportcsv()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/exportCSV');
    }

}

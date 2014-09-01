<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErreursController extends Controller
{
    /**
    * Génération du tableau à exporter
    *
    * @param  Symfony\Component\HttpFoundation\Request  $request
    * 
    * @return View
    */
    public function curlAction( Request $request )
    {
        $handle = curl_init('http://www.gfrezgzregergezrgezrgeoogle.com/');
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for not 200 */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode >= 400 || $httpCode === 0) {
            echo "t'es tout cassé toi";
        }

        curl_close($handle);

        return new Response('{"success":true}', 200);
    }
}

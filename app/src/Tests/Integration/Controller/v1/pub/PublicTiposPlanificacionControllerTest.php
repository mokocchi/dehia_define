<?php

namespace App\Tests\Integration\Controller\v1\pub;

use App\Entity\TipoPlanificacion;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicTiposPlanificacionControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private function createTipoPlanificacion(string $nombre) {
        $tipoPlanificacion = new TipoPlanificacion();
        $tipoPlanificacion->setNombre($nombre);
        return $tipoPlanificacion;
    }
    
    public function test_get_all()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createTipoPlanificacion("TipoPlanificacion" . $i));
            $this->entityManager->flush();
        }

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_tipos_planificacion')
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        //+Secuencial, Bifurcada, Libre
        $this->assertEquals(28, count($data["results"]));
        $this->assertEquals("TipoPlanificacion11", $data["results"][14]["nombre"]);
    }
}
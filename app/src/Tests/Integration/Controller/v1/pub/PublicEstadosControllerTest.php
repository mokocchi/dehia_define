<?php

namespace App\Tests\Integration\Controller\v1\pub;

use App\Entity\Estado;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicEstadosControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private function createEstado(string $nombre) {
        $estado = new Estado();
        $estado->setNombre($nombre);
        return $estado;
    }
    
    public function test_get_all()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createEstado("Estado" . $i));
            $this->entityManager->flush();
        }

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_estados')
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        //+Público, Privado
        $this->assertEquals(27, count($data["results"]));
        $this->assertEquals("Estado11", $data["results"][13]["nombre"]);
    }
}
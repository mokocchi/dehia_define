<?php

namespace App\Tests\Integration\Controller\v1\pub;

use App\Entity\TipoTarea;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicTiposTareaControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private function createTipoTarea(string $nombre) {
        $tipoTarea = new TipoTarea();
        $tipoTarea->setNombre($nombre);
        $tipoTarea->setCodigo(strtolower($nombre));
        return $tipoTarea;
    }
    
    public function test_get_all()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createTipoTarea("TipoTarea" . $i));
            $this->entityManager->flush();
        }

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_tipos_tarea')
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        //+10 tipos
        $this->assertEquals(35, count($data["results"]));
        $this->assertEquals("TipoTarea11", $data["results"][21]["nombre"]);
    }
}
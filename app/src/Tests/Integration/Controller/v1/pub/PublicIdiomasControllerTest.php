<?php

namespace App\Tests\Integration\Controller\v1\pub;

use App\Entity\Idioma;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicIdiomasControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private function createIdioma(string $nombre) {
        $idioma = new Idioma();
        $idioma->setNombre($nombre);
        $idioma->setCode(substr($nombre, 0, 2));
        return $idioma;
    }
    
    public function test_get_all()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createIdioma("Idioma" . $i));
            $this->entityManager->flush();
        }

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_idiomas')
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        //+Español, Inglés, Japonés
        $this->assertEquals(28, count($data["results"]));
        $this->assertEquals("Idioma11", $data["results"][14]["nombre"]);
    }
}
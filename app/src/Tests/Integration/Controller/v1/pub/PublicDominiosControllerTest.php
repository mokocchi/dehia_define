<?php

namespace App\Tests\Integration\Controller\v1\pub;

use App\Entity\Dominio;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicDominiosControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private function createDominio(string $nombre) {
        $dominio = new Dominio();
        $dominio->setNombre($nombre);
        return $dominio;
    }
    
    public function test_get_all_query_filter()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createDominio("Test" . $i));
            $this->entityManager->flush();
        }

        $this->entityManager->persist($this->createDominio("wontmatch" . $i));
        $this->entityManager->flush();

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_dominios', ["nombre" => "Test"])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("Test11", $data["results"][11]["nombre"]);
    }

    public function test_get_all_no_filter()
    {
        for ($i=0; $i < 25; $i++) { 
            $this->entityManager->persist($this->createDominio("Test" . $i));
            $this->entityManager->flush();
        }

        $this->entityManager->persist($this->createDominio("wontmatch" . $i));
        $this->entityManager->flush();

        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('get_dominios')
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        //+{id:1, nombre:"Pruebas"}
        $this->assertEquals(27, count($data["results"]));
        $this->assertEquals("Test11", $data["results"][12]["nombre"]);
    }
}
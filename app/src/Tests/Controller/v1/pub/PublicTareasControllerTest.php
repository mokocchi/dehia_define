<?php

namespace App\Test\Controller\v1\pub;

use App\Entity\Dominio;
use App\Entity\Tarea;
use App\Tests   \ApiTestCase;

class PublicTareasControllerTest extends ApiTestCase
{
    private static $autorEmail = "autor@test.com";
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $dominio = new Dominio();
        $dominio->setNombre(self::$dominioName);
        self::$em->persist($dominio);
        self::$em->flush();
        self::$dominioId = $dominio->getId();
        self::$resourceUri = self::$prefijo_api . "/public/tareas";
        self::createAutor(self::$autorEmail);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        self::truncateEntities([Tarea::class]);
        self::$em->clear();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::truncateEntities([Dominio::class]);
        self::removeUsuarios();
    }
    /** @group getAll */
    public function testGetAll()
    {
        for ($i = 0; $i < 25; $i++) {
            $this->createTarea([
                "nombre" => "Tarea test",
                "codigo" => self::$tareaCodigo . $i,
                "consigna" => "Probar la paginación de las tareas",
                "tipo" => "simple",
                "estado" => "Público",
                "autor" => self::$autorEmail
            ]);
        }

        $this->createActividad([
            "nombre" => "Tarea not match",
            "codigo" => "codigo",
            "tipo" => "simple",
            "objetivo" => "Probar la paginación de las tareas",
            "estado" => "Público",
            "autor" => self::$autorEmail
        ]);

        $this->createActividad([
            "nombre" => "Tarea test",
            "codigo" => "codigo",
            "tipo" => "simple",
            "objetivo" => "Probar la paginación de las tareas",
            "autor" => self::$autorEmail
        ]);
        $uri = self::$resourceUri . '?nombre=test';

        $response = self::$client->get($uri);
        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->getJson($response);
        $this->assertEquals(self::$tareaCodigo . 5, $data["results"][5]["codigo"]);
        $this->assertEquals(10, $data["count"]);
        $this->assertEquals(25, $data["total"]);
        $this->assertArrayHasKey("_links", $data);
        $this->assertArrayHasKey("next", $data["_links"]);
        $nextLink = $data["_links"]["next"];
        $response = self::$client->get($nextLink);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->getJson($response);
        $this->assertEquals(self::$tareaCodigo . 15, $data["results"][5]["codigo"]);
        $this->assertEquals(10, $data["count"]);
        $this->assertEquals(10, $data["count"]);

        $this->assertArrayHasKey("_links", $data);
        $this->assertArrayHasKey("last", $data["_links"]);
        $lastLink = $data["_links"]["last"];
        $response = self::$client->get($lastLink);
        $data = $this->getJson($response);
        $this->assertEquals(5, $data["count"]);
        $this->assertEquals(5, count($data["results"]));
    }
}

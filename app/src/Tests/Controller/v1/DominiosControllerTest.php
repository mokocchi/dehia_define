<?php

namespace App\Tests\Controller;

use App\Entity\Dominio;
use App\Tests\ApiTestCase;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DominioControllerTest extends ApiTestCase
{
    private static $autorEmail = "autor@test.com";
    private static $usuarioEmail = "usuario@test.com";

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$resourceUri = self::$prefijo_api . "/dominios";
        self::createAutor(self::$autorEmail);
        self::$access_token = "1";
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::truncateEntities([Dominio::class]);
        self::removeUsuario(self::$usuarioEmail);
    }

    public static function tearDownAfterClass(): void
    {
        self::removeUsuarios();
    }

    public function testPostDominioAction()
    {
        $options = [
            'headers' => ['Authorization' => "Bearer 1"],
            'json' => [
                "nombre" => self::$dominioName,
            ]
        ];

        $response = self::$client->post(self::$resourceUri, $options);
        $this->assertTrue($response->hasHeader("Location"));
        $data = $this->getJson($response);
        $this->assertArrayHasKey("nombre", $data);
        $this->assertEquals(self::$dominioName, $data["nombre"]);
    }

    public function testPostTwice()
    {
        $this->createDominio();

        $options = [
            'headers' => ['Authorization' => "Bearer 1"],
            'json' => [
                "nombre" => self::$dominioName,
            ]
        ];

        try {
            self::$client->post(self::$resourceUri, $options);
            $this->fail("No se detectó el dominio repetido");
        } catch (RequestException $e) {
            self::assertErrorResponse($e->getResponse(), Response::HTTP_BAD_REQUEST, "Ya existe un dominio con el mismo nombre");
            $dominios = self::$em->getRepository(Dominio::class)->findBy(["nombre" => self::$dominioName]);
            $this->assertEquals(1, count($dominios));
        }
    }

    public function testPostUnauthorized()
    {
        $this->assertUnauthorized(Request::METHOD_POST, self::$resourceUri);
    }

    public function testPostForbidden()
    {
        $this->assertForbidden(Request::METHOD_POST, self::$resourceUri, "3");
    }

    public function testPostWrongToken()
    {
        $this->assertWrongToken(Request::METHOD_POST, self::$resourceUri);
    }

    /** @group failing */
    public function testPostNoJson()
    {
        $this->assertNoJson(Request::METHOD_POST, self::$resourceUri);
    }

    public function testPostNoNombre()
    {
        $options = [
            "headers" => ["Authorization" => "Bearer 1"],
            "json" => []
        ];
        try {
            self::$client->post(self::$resourceUri, $options);
            $this->fail("No se detectó que no se envió un nombre");
        } catch (RequestException $e) {
            self::assertErrorResponse($e->getResponse(), Response::HTTP_BAD_REQUEST, "Uno o más de los campos requeridos falta o es nulo");
        }
    }
}

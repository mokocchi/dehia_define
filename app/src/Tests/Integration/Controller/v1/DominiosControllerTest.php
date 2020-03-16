<?php

namespace App\Tests\Integration\Controller\v1;

use App\Entity\Dominio;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class DominiosControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;

    private static $dominioName = "Pruebas";

    protected function createDominio(string $nombre): int
    {
        $dominio = new Dominio();
        $dominio->setNombre(is_null($nombre) ? self::$dominioName : $nombre);
        $this->entityManager->persist($dominio);
        $this->entityManager->flush();
        return $dominio->getId();
    }

    public function test_dominio_is_saved_in_database_when_submitted_valid_form()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode(
                ["nombre" => $nombre]
            )
        );


        $response = $httpClient->getResponse();
        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(["id", "nombre"], array_keys($data));

        $dominio = $repository->findOneBy(["nombre" => $nombre]);
        $this->assertNotNull($dominio);
    }

    public function test_dominio_is_not_saved_when_the_name_already_exists()
    {
        $this->createDominio(self::$dominioName);
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode(
                ["nombre" => $nombre]
            )
        );


        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(1, count($dominios));
    }

    public function test_dominio_is_not_saved_when_unauthorized()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            [],
            json_encode(
                ["nombre" => $nombre]
            )
        );

        $response = $httpClient->getResponse();
        $this->assertSame(401, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(0, count($dominios));
    }

    public function test_dominio_is_not_saved_when_forbidden_role()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 3"],
            json_encode(
                ["nombre" => $nombre]
            )
        );

        $response = $httpClient->getResponse();
        $this->assertSame(403, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(0, count($dominios));
    }

    public function test_dominio_is_not_saved_when_given_wrong_token()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 0"],
            json_encode(
                ["nombre" => $nombre]
            )
        );

        $response = $httpClient->getResponse();
        $this->assertSame(401, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(0, count($dominios));
    }

    public function test_dominio_is_not_saved_when_no_json_sent()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"]
        );

        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(0, count($dominios));
    }

    public function test_dominio_is_not_saved_when_no_name_sent()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Dominio::class);

        $nombre = self::$dominioName;

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_dominio'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode(
                ["nombre" => null]
            )
        );

        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $dominios = $repository->findBy(["nombre" => $nombre]);
        $this->assertEquals(0, count($dominios));
    }
}

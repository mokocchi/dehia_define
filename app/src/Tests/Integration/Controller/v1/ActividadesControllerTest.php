<?php

namespace App\Tests\Integration\Controller\v1;

use App\Entity\Actividad;
use App\Entity\Dominio;
use App\Entity\Estado;
use App\Entity\Idioma;
use App\Entity\Planificacion;
use App\Entity\TipoPlanificacion;
use App\Tests\Support\Database;
use App\Tests\Support\HttpClient;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ActividadesControllerTest extends TestCase
{
    use Kernel, HttpClient, Router, Database;
    public function test_actividad_is_saved_in_database_when_submitted_valid_form()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 1,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals([
            "id",
            "nombre",
            "objetivo",
            "idioma",
            "dominio",
            "tipo_planificacion",
            "autor",
            "estado",
            "codigo",
            "_links"
        ], array_keys($data));
        $this->assertNotEmpty($data["id"]);
        $this->assertEquals("Actividad test", $data["nombre"]);
        $this->assertEquals("Probar crear una actividad", $data["objetivo"]);
        $this->assertEquals("1234", $data["codigo"]);
        $this->assertEquals("Pruebas", $data["dominio"]["nombre"]);
        $this->assertEquals("es", $data["idioma"]["code"]);
        $this->assertEquals("Secuencial", $data["tipo_planificacion"]["nombre"]);
        $this->assertEquals("Privado", $data["estado"]["nombre"]);
        $this->assertEquals("Autor", $data["autor"]["nombre"]);
        $this->assertEquals($this->generateUrl("show_actividad", ["id" => $data["id"]]), $data['_links']['self']);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNotNull($actividad);
    }

    public function test_actividad_is_not_saved_in_database_when_submitted_invalid_form()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Se recibieron datos inválidos", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }

    public function test_actividad_is_not_saved_in_database_when_codigo_alreade_exists()
    {
        $httpClient = $this->createClient();
        $actividad = new Actividad();
        $actividad->setNombre("Actividad test");
        $actividad->setObjetivo("Probar el guardado de actividades repetidas");
        $actividad->setCodigo("1234");

        $dominio = $this->entityManager->getRepository(Dominio::class)->find(1);
        $actividad->setDominio($dominio);

        $idioma = $this->entityManager->getRepository(Idioma::class)->find(1);
        $actividad->setIdioma($idioma);

        $tipoPlanificacion = $this->entityManager->getRepository(TipoPlanificacion::class)->find(1);
        $actividad->setTipoPlanificacion($tipoPlanificacion);

        $estado = $this->entityManager->getRepository(Estado::class)->find(1);
        $actividad->setEstado($estado);

        $planificacion = new Planificacion();
        $this->entityManager->persist($planificacion);
        $this->entityManager->flush();
        $actividad->setPlanificacion($planificacion);

        $this->entityManager->persist($actividad);
        $this->entityManager->flush();

        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Ya existe una actividad con el mismo código", $data["developer_message"]);

        $actividades = $repository->findBy(["codigo" => "1234"]);
        $this->assertLessThan(2, count($actividades));
    }

    public function test_actividad_is_not_saved_when_unauthorized()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            [],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(401, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Se requiere autenticación OAuth", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }

    public function test_actividad_is_not_saved_when_forbidden_role()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 3"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(403, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("El token no pertenece a un autor", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }

    public function test_actividad_is_not_saved_when_given_wrong_token()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 0"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "codigo" => "1234",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(401, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("El token expiró o es inválido", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }

    public function test_actividad_is_not_saved_when_no_json_sent()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"]
        );


        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("No hay campos en el json", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }

    public function test_actividad_is_not_saved_when_no_codigo_sent()
    {
        $httpClient = $this->createClient();
        $repository = $this->entityManager->getRepository(Actividad::class);

        $httpClient->request(
            Request::METHOD_POST,
            $this->generateUrl('post_actividad'),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"],
            json_encode([
                "nombre" => "Actividad test",
                "objetivo" => "Probar crear una actividad",
                "dominio" => 99,
                "idioma" => 1,
                "tipoPlanificacion" => 1,
                "estado" => 2
            ])
        );


        $response = $httpClient->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Uno o más de los campos requeridos falta o es nulo", $data["developer_message"]);

        $actividad = $repository->findOneBy(["codigo" => "1234"]);
        $this->assertNull($actividad);
    }
}

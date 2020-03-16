<?php

namespace App\Tests\Integration\Controller\v1;

use App\Entity\Actividad;
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
}

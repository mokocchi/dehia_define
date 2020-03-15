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
    public function test_dominio_is_saved_in_database_when_submitted_valid_form()
    {
        $httpClient = $this->createClient();

        $httpClient->request(
            Request::METHOD_GET,
            $this->generateUrl('show_actividad', ["id" => 1]),
            [],
            [],
            ["HTTP_AUTHORIZATION" => "Bearer 1"]
        );


        $response = $httpClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        // $this->assertEquals(["id", "nombre"], array_keys($data));
    }
}

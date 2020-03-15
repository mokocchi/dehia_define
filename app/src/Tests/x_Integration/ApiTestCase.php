<?php

namespace App\Tests;

use App\Entity\Actividad;
use App\Entity\Dominio;
use App\Entity\Estado;
use App\Entity\Idioma;
use App\Entity\Planificacion;
use App\Entity\Tarea;
use App\Entity\TipoPlanificacion;
use App\Entity\TipoTarea;
use App\Entity\Autor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends KernelTestCase
{
    protected static $client;
    protected static $access_token;
    protected static $prefijo_api = '/api/v1.0';
    protected static $resourceUri;
    protected static $actividadCodigo = "actividadtest";
    protected static $tareaCodigo = "tareatest";
    protected static $dominioName = "Test";
    protected static $dominioId;

    protected static $apiProblemArray = [
        "status",
        "developer_message",
        "user_message",
        "error_code",
        "more_info"
    ];
    protected static $em;

    protected static function getAuthHeader()
    {
        return 'Bearer ' . self::$access_token;
    }

    protected static function getDefaultOptions()
    {
        return ["headers" => ['Authorization' => self::getAuthHeader()]];
    }

    private function assertApiProblemResponse($response, $message)
    {
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::$apiProblemArray, array_keys($data));
        $this->assertEquals($message, $data["developer_message"]);
    }

    protected function assertErrorResponse($response, $statusCode, $message)
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertApiProblemResponse($response, $message);
    }

    protected function dumpError(RequestException $e)
    {
        $data = json_decode((string) $e->getResponse()->getBody(), true);
        dd($data["user_message"]);
    }

    protected static function createAutor(string $email)
    {
        return self::createUsuario([
            "email" => $email,
            "nombre" => "Pedro",
            "apellido" => "Sánchez",
            "googleid" => "1001",
            "role" => "ROLE_AUTOR"
        ]);
    }

    protected static function createUsuarioApp($email)
    {
        return self::createUsuario([
            "email" => "$email",
            "nombre" => "María",
            "apellido" => "Del Carril",
            "googleid" => "2001",
            "role" => "ROLE_USUARIO_APP"
        ]);
    }

    protected static function createUsuario(array $usuarioArray)
    {
        $user = new Autor();
        $user->setEmail($usuarioArray["email"]);
        $user->setNombre($usuarioArray["nombre"]);
        $user->setApellido($usuarioArray["apellido"]);
        $user->setGoogleid($usuarioArray["googleid"]);
        $user->addRole($usuarioArray["role"]);

        self::$em->persist($user);
        self::$em->flush();
        return $user;
    }

    /**
     * @param array $actividad_array Array of nombre, objetivo, codigo and maybe autor, maybe estado
     */
    protected function createActividad(array $actividad_array): Actividad
    {
        $actividad = new Actividad();
        $actividad->setNombre($actividad_array["nombre"]);
        $actividad->setObjetivo($actividad_array["objetivo"]);
        $actividad->setCodigo($actividad_array["codigo"]);
        $dominio = self::$em->getRepository(Dominio::class)->find(self::$dominioId);
        $actividad->setDominio($dominio);
        $idioma = self::$em->getRepository(Idioma::class)->findOneBy(["code" => "es"]);
        $actividad->setIdioma($idioma);
        $tipoPlanificacion = self::$em->getRepository(TipoPlanificacion::class)->findOneBy(["nombre" => "Secuencial"]);
        $actividad->setTipoPlanificacion($tipoPlanificacion);
        $planificacion = new Planificacion();
        self::$em->persist($planificacion);
        $actividad->setPlanificacion($planificacion);
        if (array_key_exists("estado", $actividad_array)) {
            $estado = self::$em->getRepository(Estado::class)->findOneBy(["nombre" => $actividad_array["estado"]]);
        } else {
            $estado = self::$em->getRepository(Estado::class)->findOneBy(["nombre" => "Privado"]);
        }
        $actividad->setEstado($estado);
        $autor = self::$em->getRepository(Autor::class)->findOneBy(["email" => $actividad_array["autor"]]);
        $actividad->setAutor($autor);
        self::$em->persist($actividad);
        self::$em->flush();
        return $actividad;
    }

    protected function createDefaultActividad(): Actividad
    {
        return $this->createActividad([
            "nombre" => "Actividad test",
            "objetivo" => "Probar crear una actividad",
            "codigo" => self::$actividadCodigo,
        ]);
    }

    /**
     * @param array $tareaArray Array of nombre, consigna, codigo, tipo and maybe autor, maybe estado
     */
    protected function createTarea(array $tareaArray): Tarea
    {
        $tarea = new Tarea();
        $tarea->setNombre($tareaArray["nombre"]);
        $tarea->setConsigna($tareaArray["consigna"]);
        $tarea->setCodigo($tareaArray["codigo"]);
        $dominio = self::$em->getRepository(Dominio::class)->find(self::$dominioId);
        $tarea->setDominio($dominio);
        $tipoTarea = self::$em->getRepository(TipoTarea::class)->findOneBy(["codigo" => $tareaArray["tipo"]]);
        $tarea->setTipo($tipoTarea);
        $autor = self::$em->getRepository(Autor::class)->findOneBy(["email" => $tareaArray["autor"]]);
        $tarea->setAutor($autor);
        if (array_key_exists("estado", $tareaArray)) {
            $estado = self::$em->getRepository(Estado::class)->findOneBy(["nombre" => $tareaArray["estado"]]);
        } else {
            $estado = self::$em->getRepository(Estado::class)->findOneBy(["nombre" => "Privado"]);
        }
        $tarea->setEstado($estado);
        self::$em->persist($tarea);
        self::$em->flush();
        return $tarea;
    }

    protected function createDefaultTarea(): Tarea
    {
        return $this->createTarea([
            "nombre" => "Tarea test",
            "consigna" => "Probar las tareas",
            "codigo" => self::$tareaCodigo,
            "tipo" => "simple"
        ]);
    }

    protected function createDominio(?string $nombre = null): int
    {
        $dominio = new Dominio();
        $dominio->setNombre(is_null($nombre) ? self::$dominioName : $nombre);
        self::$em->persist($dominio);
        self::$em->flush();
        return $dominio->getId();
    }

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$client = new Client(
            [
                'base_uri' => 'http://define.nginx/'
            ]
        );
        self::$em = self::getService("doctrine")->getManager();
    }

    protected static function removeUsuarios()
    {
        self::truncateEntities([Autor::class]);
    }

    protected static function removeUsuario($email)
    {
        $usuario = self::$em->getRepository(Autor::class)->findOneBy(["email" => $email]);
        if ($usuario) {
            self::$em->remove($usuario);
            self::$em->flush();
        }
    }

    protected function tearDown(): void
    {
    }

    protected static function getService($id)
    {
        return self::$kernel->getContainer()->get($id);
    }

    protected static function truncateTable($name)
    {
        $connection = self::$em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        $truncateSql = $platform->getTruncateTableSQL($name);
        $connection->executeUpdate($truncateSql);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
    }

    protected static function truncateEntities(array $entities)
    {
        $connection = self::$em->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                self::$em->getClassMetadata($entity)->getTableName()
            );
            $connection->executeUpdate($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function assertUnauthorized($method, $uri)
    {
        try {
            switch ($method) {
                case Request::METHOD_GET:
                    self::$client->get($uri);
                    break;
                case Request::METHOD_POST:
                    self::$client->post($uri);
                    break;
                case Request::METHOD_PATCH:
                    self::$client->patch($uri);
                    break;
                case Request::METHOD_PUT:
                    self::$client->put($uri);
                    break;
                case Request::METHOD_DELETE:
                    self::$client->delete($uri);
                default:
                    break;
            }
            $this->fail("No se detectó una petición no autorizada");
        } catch (RequestException $e) {
            self::assertErrorResponse($e->getResponse(), Response::HTTP_UNAUTHORIZED, 'Se requiere autenticación OAuth');
        }
    }

    protected function assertForbidden($method, $uri, $access_token)
    {
        $options = [
            "headers" => ["Authorization" => "Bearer " . $access_token]
        ];
        try {
            switch ($method) {
                case Request::METHOD_GET:
                    self::$client->get($uri, $options);
                    break;
                case Request::METHOD_POST:
                    self::$client->post($uri, $options);
                    break;
                case Request::METHOD_PUT:
                    self::$client->put($uri, $options);
                    break;
                case Request::METHOD_PATCH:
                    self::$client->patch($uri, $options);
                    break;
                case Request::METHOD_DELETE:
                    self::$client->delete($uri, $options);
                default:
                    break;
            }
            $this->fail("No se detectó una petición sin permisos suficientes");
        } catch (RequestException $e) {
            $this->assertErrorResponse($e->getResponse(), Response::HTTP_FORBIDDEN, "El token no pertenece a un autor");
        }
    }

    protected function assertWrongToken($method, $uri)
    {
        $options = [
            "headers" => ["Authorization" => "Bearer %token%"]
        ];
        try {
            switch ($method) {
                case Request::METHOD_GET:
                    self::$client->get($uri, $options);
                    break;
                case Request::METHOD_POST:
                    self::$client->post($uri, $options);
                    break;
                case Request::METHOD_PUT:
                    self::$client->put($uri, $options);
                    break;
                case Request::METHOD_PATCH:
                    self::$client->patch($uri, $options);
                    break;
                case Request::METHOD_DELETE:
                    self::$client->delete($uri, $options);
                default:
                    break;
            }
            $this->fail("No se detectó una petición con un token erróneo");
        } catch (RequestException $e) {
            $this->assertErrorResponse($e->getResponse(), Response::HTTP_UNAUTHORIZED, "El token expiró o es inválido");
        }
    }

    protected function assertNoJson($method, $uri)
    {
        $options = [
            'headers' => ['Authorization' => self::getAuthHeader()]
        ];
        try {
            switch ($method) {
                case Request::METHOD_POST:
                    self::$client->post($uri, $options);
                    break;
                case Request::METHOD_PUT:
                    self::$client->put($uri, $options);
                    break;
                case Request::METHOD_PATCH:
                    self::$client->patch($uri, $options);
                    break;
                default:
                    break;
            }
            $this->fail("No se detectó que no hay json en el request");
        } catch (RequestException $e) {
            $this->assertErrorResponse($e->getResponse(), Response::HTTP_BAD_REQUEST, "No hay campos en el json");
        }
    }

    public function assertNotFound($method, $uri, $className)
    {
        try {
            switch ($method) {
                case Request::METHOD_GET:
                    self::$client->get($uri, self::getDefaultOptions());
                case Request::METHOD_PATCH:
                    $options = [
                        "headers" => ["Authorization" => self::getAuthHeader()],
                        "json" => []
                    ];
                    self::$client->patch($uri, $options);
                    break;
                case Request::METHOD_PUT:
                    $options = [
                        "headers" => ["Authorization" => self::getAuthHeader()],
                        "json" => []
                    ];
                    self::$client->put($uri, $options);
                    break;
                case Request::METHOD_DELETE:
                    self::$client->delete($uri, self::getDefaultOptions());
                    break;
                default:
                    break;
            }
        } catch (RequestException $e) {
            $this->assertErrorResponse($e->getResponse(), Response::HTTP_NOT_FOUND, sprintf("No se encontró: %s 0", $className));
        }
    }

    protected function getJson($response)
    {
        return json_decode((string) $response->getBody(), true);
    }
}

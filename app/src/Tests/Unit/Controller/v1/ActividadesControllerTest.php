<?php

namespace App\Tests\Controller\v1;

use App\Api\ApiProblemException;
use App\Controller\v1\ActividadesController;
use App\Entity\Actividad;
use App\Entity\Autor;
use App\Entity\Dominio;
use App\Entity\Estado;
use App\Entity\Idioma;
use App\Entity\Planificacion;
use App\Entity\TipoPlanificacion;
use App\Repository\ActividadRepository;
use App\Tests\Support\Database;
use App\Tests\Support\Kernel;
use App\Tests\Support\Router;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActividadesControllerTest extends TestCase
{
    use Kernel;
    use Router;

    /** @var LoggerInterface&MockObject $loggerMock */
    private $loggerMock;

    /** @var SerializerInterface&MockObject $serializerMock */
    private $serializerMock;

    public function setUp()
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
    }

    /**
     * @param string $name
     * @return Request
     */
    private function makeActividadAddRequest(array $actividadArray): Request
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode(
                [
                    "nombre" => $actividadArray["nombre"],
                    "objetivo" => $actividadArray["objetivo"],
                    "codigo" => $actividadArray["codigo"],
                    "dominio" => 1,
                    "idioma" => 1,
                    "tipoPlanificacion" => 1,
                    "estado" => 2
                ]
            )
        );

        $request->setMethod(Request::METHOD_POST);

        $request->setRequestFormat('json');

        return $request;
    }

    public function testPostActividadAction()
    {
        $request = $this->makeActividadAddRequest([
            "nombre" => "Actividad test",
            "objetivo" => "Probar crear una actividad",
            "codigo" => "1234",
        ]);

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var ActividadRepository&MockObject $actividadRepositoryMock */
        $actividadRepositoryMock = $this->createMock(ActividadRepository::class);
        $actividadRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($actividadRepositoryMock);
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('persist');
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('flush');

        $controller = new ActividadesController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->postActividadAction($request, $entityManagerMock);
        $data = json_decode($result->getContent(), true);
        $this->assertArrayHasKey("nombre", $data);
        $this->assertEquals("Actividad test", $data["nombre"]);
    }

    public function testPostActividadNoJson()
    {
        $request = new Request();
        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $controller = new ActividadesController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        try {
            $controller->postActividadAction($request, $entityManagerMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("No hay campos en el json", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Hubo un problema con la petición", $apiProblem->getUserMessage());
        }
    }

    public function testShowActividadAction()
    {
        $request = new Request();
        $request->setRequestFormat('json');
        $id = 1;
        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        $actividad = new Actividad();
        $actividad->setId(0);
        $actividad->setNombre("Actividad test");
        $actividad->setObjetivo("Probar la consulta de actividades");
        $actividad->setCodigo("1234");

        $dominio = new Dominio();
        $dominio->setNombre("Test");
        $actividad->setDominio($dominio);

        $idioma = new Idioma();
        $idioma->setId(0);
        $idioma->setNombre("Francés");
        $idioma->setCode("fr");
        $actividad->setIdioma($idioma);

        $tipoPlanificacion = new TipoPlanificacion();
        $tipoPlanificacion->setId(0);
        $tipoPlanificacion->setNombre("Circular");
        $actividad->setTipoPlanificacion($tipoPlanificacion);

        $estado = new Estado();
        $estado->setNombre("Privado");
        $actividad->setEstado($estado);

        $planificacion = new Planificacion();
        $planificacion->setId(0);
        $actividad->setPlanificacion($planificacion);

        $autor = new Autor();
        $autor->setNombre("Autor");
        $actividad->setAutor($autor);

        /** @var ActividadRepository&MockObject $actividadArray */
        $actividadRepositoryMock = $this->createMock(ActividadRepository::class);
        $actividadRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('find')
            ->willReturn($actividad);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($actividadRepositoryMock);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $controller = new ActividadesController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->showActividadAction($id, $request, $entityManagerMock);
        $data = json_decode($result->getContent(), true);
        $this->assertEquals(
            [
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
            ], array_keys($data)
        );
        $this->assertNotNull($data["id"]);
        $this->assertEquals("Actividad test", $data["nombre"]);
        $this->assertEquals("Probar la consulta de actividades", $data["objetivo"]);
        $this->assertEquals("1234", $data["codigo"]);
        $this->assertEquals("Test", $data["dominio"]["nombre"]);
        $this->assertEquals("fr", $data["idioma"]["code"]);
        $this->assertEquals("Circular", $data["tipo_planificacion"]["nombre"]);
        $this->assertEquals("Privado", $data["estado"]["nombre"]);
        $this->assertEquals("Autor", $data["autor"]["nombre"]);
        $this->assertEquals($this->generateUrl("show_actividad", ["id" => $data["id"]]), $data['_links']['self']);
    }

    
}

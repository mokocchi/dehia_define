<?php

namespace App\Tests\Unit\Controller\v1;

use App\Api\ApiProblemException;
use App\Controller\v1\DominiosController;
use App\Entity\Dominio;
use App\Repository\DominioRepository;
use App\Tests\Support\Database;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class DominiosControllerTest extends TestCase
{
    use Kernel;

    private static $dominioName = "Test";

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
    private function makeDominioAddRequest(?string $name): Request
    {
        $request = new Request([], [], [], [], [], [], json_encode(["nombre" => $name]));

        $request->setMethod(Request::METHOD_POST);

        $request->setRequestFormat('json');

        return $request;
    }

    public function testPostDominioAction()
    {
        $request = $this->makeDominioAddRequest(self::$dominioName);

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var DominioRepository&MockObject $dominioRepositoryMock */
        $dominioRepositoryMock = $this->createMock(DominioRepository::class);
        $dominioRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($dominioRepositoryMock);
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('persist');
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('flush');

        $controller = new DominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->postDominioAction($request, $entityManagerMock);
        $data = json_decode($result->getContent(), true);
        $this->assertArrayHasKey("nombre", $data);
        $this->assertEquals(self::$dominioName, $data["nombre"]);
    }

    public function testCreateDominioTwice()
    {
        $request = $this->makeDominioAddRequest(self::$dominioName);
        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        $existantDominio = new Dominio();
        $existantDominio->setId(1);
        $existantDominio->setNombre(self::$dominioName);

        /** @var DominioRepository&MockObject $dominioRepositoryMock */
        $dominioRepositoryMock = $this->createMock(DominioRepository::class);
        $dominioRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn($existantDominio);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($dominioRepositoryMock);
        $entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $controller = new DominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        try {
            $controller->postDominioAction($request, $entityManagerMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("Ya existe un dominio con el mismo nombre", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Ya existe un dominio con el mismo nombre", $apiProblem->getUserMessage());
        }
    }

    public function testPostDominioNoJson()
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

        $controller = new DominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        try {
            $controller->postDominioAction($request, $entityManagerMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("No hay campos en el json", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Hubo un problema con la petición", $apiProblem->getUserMessage());
        }
    }

    public function testPostDominioNoNombre()
    {
        $request = $this->makeDominioAddRequest(null);
        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->expects($this->never())
            ->method('persist');
        $entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $controller = new DominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        try {
            $controller->postDominioAction($request, $entityManagerMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("Uno o más de los campos requeridos falta o es nulo", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Faltan datos", $apiProblem->getUserMessage());
        }
    }
}

<?php

namespace App\Tests\Unit\Controller\v1\pub;

use App\Controller\v1\pub\PublicDominiosController;
use App\Entity\Dominio;
use App\Repository\DominioRepository;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PublicDominiosControllerTest extends TestCase
{
    use Kernel;

    /** @var LoggerInterface&MockObject $loggerMock */
    private $loggerMock;

    /** @var SerializerInterface&MockObject $serializerMock */
    private $serializerMock;

    public function setUp()
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
    }

    private function createDominio(string $nombre) {
        $dominio = new Dominio();
        $dominio->setNombre($nombre);
        return $dominio;
    }

    public function testGetAllQueryFilter()
    {
        $dominiosMatch = [];
        for ($i=0; $i < 25; $i++) { 
            $dominiosMatch[] = $this->createDominio("Test" . $i);
        }

        $request = new Request(["nombre" => "Test"]);
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var DominioRepository&MockObject $dominioRepositoryMock */
        $dominioRepositoryMock = $this->createMock(DominioRepository::class);
        $dominioRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findNombreLike')
            ->willReturn($dominiosMatch);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($dominioRepositoryMock);

        $controller = new PublicDominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getDominiosAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("Test11", $data["results"][11]["nombre"]);
    }

    public function testGetAllNoFilter()
    {
        $dominiosMatch = [];
        for ($i=0; $i < 25; $i++) { 
            $dominiosMatch[] = $this->createDominio("Test" . $i);
        }

        $request = new Request();
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var DominioRepository&MockObject $dominioRepositoryMock */
        $dominioRepositoryMock = $this->createMock(DominioRepository::class);
        $dominioRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findAll')
            ->willReturn($dominiosMatch);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($dominioRepositoryMock);

        $controller = new PublicDominiosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getDominiosAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("Test11", $data["results"][11]["nombre"]);
    }
}
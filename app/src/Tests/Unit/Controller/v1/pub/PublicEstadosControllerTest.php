<?php

namespace App\Tests\Unit\Controller\v1\pub;

use App\Controller\v1\pub\PublicEstadosController;
use App\Entity\Estado;
use App\Repository\EstadoRepository;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PublicEstadosControllerTest extends TestCase
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

    private function createEstado(string $nombre)
    {
        $estado = new Estado();
        $estado->setNombre($nombre);
        return $estado;
    }

    public function testGetAll()
    {
        $estados = [];
        for ($i = 0; $i < 25; $i++) {
            $estados[] = $this->createEstado("Estado" . $i);
        }

        $request = new Request();
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var EstadoRepository&MockObject $estadoRepositoryMock */
        $estadoRepositoryMock = $this->createMock(EstadoRepository::class);
        $estadoRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findAll')
            ->willReturn($estados);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($estadoRepositoryMock);

        $controller = new PublicEstadosController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getEstadosAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("Estado9", $data["results"][9]["nombre"]);
    }
}

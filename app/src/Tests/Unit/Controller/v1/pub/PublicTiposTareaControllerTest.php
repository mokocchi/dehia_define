<?php

namespace App\Tests\Unit\Controller\v1\pub;

use App\Controller\v1\pub\PublicTiposTareaController;
use App\Entity\TipoTarea;
use App\Repository\TipoTareaRepository;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PublicTiposTareaControllerTest extends TestCase
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

    private function createTipoTarea(string $nombre)
    {
        $tipoTarea = new TipoTarea();
        $tipoTarea->setNombre($nombre);
        return $tipoTarea;
    }

    public function testGetAll()
    {
        $tiposTarea = [];
        for ($i = 0; $i < 25; $i++) {
            $tiposTarea[] = $this->createTipoTarea("TipoTarea" . $i);
        }

        $request = new Request();
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var TipoTareaRepository&MockObject $tipoTareaRepositoryMock */
        $tipoTareaRepositoryMock = $this->createMock(TipoTareaRepository::class);
        $tipoTareaRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findAll')
            ->willReturn($tiposTarea);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($tipoTareaRepositoryMock);

        $controller = new PublicTiposTareaController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getTiposTareaAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("TipoTarea9", $data["results"][9]["nombre"]);
    }
}

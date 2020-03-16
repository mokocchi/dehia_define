<?php

namespace App\Tests\Unit\Controller\v1\pub;

use App\Controller\v1\pub\PublicTiposPlanificacionController;
use App\Entity\TipoPlanificacion;
use App\Repository\TipoPlanificacionRepository;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PublicTiposPlanificacionControllerTest extends TestCase
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

    private function createTipoPlanificacion(string $nombre)
    {
        $tipoPlanificacion = new TipoPlanificacion();
        $tipoPlanificacion->setNombre($nombre);
        return $tipoPlanificacion;
    }

    public function testGetAll()
    {
        $tiposPlanificacion = [];
        for ($i = 0; $i < 25; $i++) {
            $tiposPlanificacion[] = $this->createTipoPlanificacion("TipoPlanificacion" . $i);
        }

        $request = new Request();
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var TipoPlanificacionRepository&MockObject $tipoPlanificacionRepositoryMock */
        $tipoPlanificacionRepositoryMock = $this->createMock(TipoPlanificacionRepository::class);
        $tipoPlanificacionRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findAll')
            ->willReturn($tiposPlanificacion);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($tipoPlanificacionRepositoryMock);

        $controller = new PublicTiposPlanificacionController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getTiposPlanificacionAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("TipoPlanificacion9", $data["results"][9]["nombre"]);
    }
}

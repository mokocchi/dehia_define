<?php

namespace App\Tests\Unit\Controller\v1\pub;

use App\Controller\v1\pub\PublicIdiomasController;
use App\Entity\Idioma;
use App\Repository\IdiomaRepository;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PublicIdiomasControllerTest extends TestCase
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

    private function createIdioma(string $nombre)
    {
        $idioma = new Idioma();
        $idioma->setNombre($nombre);
        return $idioma;
    }

    public function testGetAll()
    {
        $idiomas = [];
        for ($i = 0; $i < 25; $i++) {
            $idiomas[] = $this->createIdioma("Idioma" . $i);
        }

        $request = new Request();
        $request->setRequestFormat('json');

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var IdiomaRepository&MockObject $idiomaRepositoryMock */
        $idiomaRepositoryMock = $this->createMock(IdiomaRepository::class);
        $idiomaRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findAll')
            ->willReturn($idiomas);

        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($idiomaRepositoryMock);

        $controller = new PublicIdiomasController($this->loggerMock, $this->serializerMock);
        $controller->setContainer(static::$container);
        $result = $controller->getIdiomasAction($request, $entityManagerMock);

        $data = json_decode($result->getContent(), true);
        $this->assertEquals(25, count($data["results"]));
        $this->assertEquals("Idioma9", $data["results"][9]["nombre"]);
    }
}

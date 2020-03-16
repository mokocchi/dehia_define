<?php

namespace App\Tests\Unit\Security;

use App\Api\ApiProblemResponseFactory;
use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Security\AuthServiceAuthenticator;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthServiceAuthenticatorTest extends TestCase
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

    public function testSupports()
    {
        /** @var ApiProblemResponseFactory&MockObject $responseFactoryMock */
        $responseFactoryMock = $this->createMock(ApiProblemResponseFactory::class);

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        $authenticator = new AuthServiceAuthenticator($entityManagerMock, $responseFactoryMock, $this->loggerMock);

        $request = new Request([], [], [], [], [], ["HTTP_AUTHORIZATION" => "Bearer 1"]);
        $this->assertTrue($authenticator->supports($request));

        $request = new Request([], [], [], [], [], ["HTTP_AUTHORIZATION" => "invalid"]);
        $this->assertFalse($authenticator->supports($request));

        $request = new Request([], [], [], [], [], []);
        $this->assertFalse($authenticator->supports($request));
    }

    public function testGetCrendentials()
    {
        /** @var ApiProblemResponseFactory&MockObject $responseFactoryMock */
        $responseFactoryMock = $this->createMock(ApiProblemResponseFactory::class);

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        $authenticator = new AuthServiceAuthenticator($entityManagerMock, $responseFactoryMock, $this->loggerMock);
        $request = new Request([], [], [], [], [], ["HTTP_AUTHORIZATION" => "Bearer 1"]);
        $this->assertEquals("Bearer 1", $authenticator->getCredentials($request));
    }

    public function testGetUser()
    {
        /** @var ApiProblemResponseFactory&MockObject $responseFactoryMock */
        $responseFactoryMock = $this->createMock(ApiProblemResponseFactory::class);

        /** @var EntityManager&MockObject $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var UserProviderInterface&MockObject $userProviderInterfaceMock */
        $userProviderInterfaceMock = $this->createMock(UserProviderInterface::class);

        /** @var AutorRepository&MockObject $autorRepositoryMock */
        $autorRepositoryMock = $this->createMock(AutorRepository::class);

        $autorRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->willReturn($autorRepositoryMock);
        $authenticator = new AuthServiceAuthenticator($entityManagerMock, $responseFactoryMock, $this->loggerMock);
        $request = new Request([], [], [], [], [], ["HTTP_AUTHORIZATION" => "Bearer 1"]);

        /** @var Autor $authenticatedAutor */
        $authenticatedAutor = $authenticator->getUser("Bearer 1", $userProviderInterfaceMock);
        $this->assertEquals("Ana", $authenticatedAutor->getNombre());
        $this->assertEquals("1001", $authenticatedAutor->getGoogleid());
    }
}

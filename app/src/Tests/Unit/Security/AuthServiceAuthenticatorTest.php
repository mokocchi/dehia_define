<?php

namespace App\Tests\Unit\Security;

use App\Api\ApiProblemException;
use App\Api\ApiProblemResponseFactory;
use App\Entity\Autor;
use App\Repository\AutorRepository;
use App\Security\AuthServiceAuthenticator;
use App\Tests\Support\Kernel;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->willReturn(null);
        $entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('flush')
            ->willReturn(null);

        /** @var Client&MockObject $clientMock */
        $clientMock = $this->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock();

        $autorArray = [
            "googleid" => "1234",
            "nombre" => "Autor",
            "apellido" => "Autórez",
            "email" => "autor@dehia.net",
            "roles" => ["ROLE_AUTOR"]
        ];

        $usuarioArray = [
            "googleid" => "1234",
            "nombre" => "Usuario",
            "apellido" => "Usuáriez",
            "email" => "usuario@dehia.net",
            "roles" => ["ROLE_USUARIO_APP"]
        ];

        $responseMock = $this->getMockBuilder(Response::class)
            ->setMethods(['getBody'])
            ->getMock();

        $responseMock
            ->expects($this->atLeastOnce())
            ->method("getBody")
            ->will($this->onConsecutiveCalls(json_encode($autorArray), json_encode($usuarioArray)));

        $clientMock
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($url, $headers) use ($responseMock) {
                $token = substr($headers["headers"]["Authorization"], 7);
                if ($token === "1") {
                    return $responseMock;
                } else {
                    $request = new Psr7Request(Request::METHOD_GET, "/api/validate");
                    $response = new Psr7Response(401, [], json_encode([
                        "developer_message" =>  'El token expiró o es inválido',
                        "user_message"  => "Error en la autenticación",
                    ]));
                    throw new RequestException("401", $request, $response);
                }
            }));

        $authenticator = new AuthServiceAuthenticator($entityManagerMock, $responseFactoryMock, $this->loggerMock, $clientMock);

        /** @var Autor $authenticatedAutor */
        $authenticatedAutor = $authenticator->getUser("Bearer 1", $userProviderInterfaceMock);
        $this->assertEquals("Autor", $authenticatedAutor->getNombre());
        $this->assertEquals("1234", $authenticatedAutor->getGoogleid());

        //onConsecutiveCalls #2 = usuarioApp
        try {
            $authenticatedAutor = $authenticator->getUser("Bearer 1", $userProviderInterfaceMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("El token no pertenece a un autor", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Ocurrió un error en la autenticación", $apiProblem->getUserMessage());
        }

        try {
            $authenticatedAutor = $authenticator->getUser("Bearer 0", $userProviderInterfaceMock);
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("El token expiró o es inválido", $apiProblem->getDeveloperMessage());
            $this->assertEquals("Error en la autenticación", $apiProblem->getUserMessage());
        }   
    }
}

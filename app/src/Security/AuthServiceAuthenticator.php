<?php

namespace App\Security;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Api\ApiProblemResponseFactory;
use App\Entity\Autor;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class AuthServiceAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $apiProblemResponseFactory;
    private $logger;

    public function __construct(EntityManagerInterface $em, ApiProblemResponseFactory $apiProblemResponseFactory, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->apiProblemResponseFactory = $apiProblemResponseFactory;
        $this->logger = $logger;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization')
            && 0 === strpos($request->headers->get('Authorization'), 'Bearer ');
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get('Authorization');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            throw new ApiProblemException(
                new ApiProblem(
                    "400",
                    "No hay credenciales",
                    "Ocurrió un error"
                )
            );
        }

        $client = new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV["AUTH_BASE_URL"]
            ]
        );

        try {
            $response = $client->get("/api/validate", ["headers" => ["Authorization" => $credentials]]);
            $data = json_decode((string) $response->getBody(), true);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    "Ocurrió un error en la autenticación",
                    "Ocurrió un error"
                )
            );
        }

        if($data["role"] != "ROLE_AUTOR") {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_FORBIDDEN,
                    "El token no pertenece a un autor",
                    "Ocurrió un error en la autenticación"
                )
            );
        }

        $autor = $this->em->getRepository(Autor::class)->findOneBy(['googleid' => $data["googleid"]]);

        if (is_null($autor)) {
            $autor = new Autor();
            $autor->setNombre($data["nombre"]);
            $autor->setApellido($data["apellido"]);
            $autor->setEmail($data["email"]);
            $autor->setGoogleid($data["googleid"]);
            $this->em->persist($autor);
            $this->em->flush();
        }
        $autor->addRole($data["role"]);

        return $autor;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->apiProblemResponseFactory->createResponse(new ApiProblem(
            "500",
            "Ocurrió un error en la autenticación",
            "Ocurrió un error"
        ));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->apiProblemResponseFactory->createResponse(new ApiProblem(
            "401",
            "No autorizado",
            "No autorizado"
        ));
    }

    public function supportsRememberMe()
    {
        return false;
    }
}

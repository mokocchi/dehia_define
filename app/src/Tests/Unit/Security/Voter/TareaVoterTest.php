<?php

namespace App\Tests\Unit\Security\Voter;

use App\Api\ApiProblemException;
use App\Entity\Tarea;
use App\Entity\Autor;
use App\Entity\Estado;
use App\Security\Voter\TareaVoter;
use App\Tests\Support\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class TareaVoterTest extends TestCase
{
    use Kernel;

    /*
     *  Casos:
     *  pública x autor = access, own
     *  pública x otro autor = access v, not own v
     *  privada x autor = access, own
     *  privada x otro autor = not access, not own
     *  
     * 
     */

    public function testGrantAccessPublicOwned()
    {
        $autor = new Autor();

        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Público");
        $tarea->setEstado($estado);
        $tarea->setAutor($autor);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        $result = $voter->vote($token, $tarea, [TareaVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantAccessPublicOther()
    {
        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Público");
        $tarea->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        $result = $voter->vote($token, $tarea, [TareaVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantOwnPublicOwned()
    {
        $autor = new Autor();

        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Público");
        $tarea->setEstado($estado);
        $tarea->setAutor($autor);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        $result = $voter->vote($token, $tarea, [TareaVoter::OWN]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testDenyOwnPublicOther()
    {
        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Público");
        $tarea->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        try {
            $voter->vote($token, $tarea, [TareaVoter::OWN]);
            $this->fail("No se detectó que la tarea no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La tarea no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la tarea", $apiProblem->getUserMessage());
        }
    }

    public function testGrantAccessPrivateToOwner()
    {
        $autor = new Autor();
        $tarea = new Tarea();
        $tarea->setAutor($autor);
        $estado = new Estado();
        $estado->setNombre("Privado");
        $tarea->setEstado($estado);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        $result = $voter->vote($token, $tarea, [TareaVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantOwnPrivateToOwner()
    {
        $autor = new Autor();
        $tarea = new Tarea();
        $tarea->setAutor($autor);
        $estado = new Estado();
        $estado->setNombre("Privado");
        $tarea->setEstado($estado);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        $result = $voter->vote($token, $tarea, [TareaVoter::OWN]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testDenyAccessPrivateOther()
    {
        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Privado");
        $tarea->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        try {
            $voter->vote($token, $tarea, [TareaVoter::ACCESS]);
            $this->fail("No se detectó que la tarea no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La tarea es privada o no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la tarea", $apiProblem->getUserMessage());
        }
    }

    public function testDenyOwnPrivateOther()
    {
        $tarea = new Tarea();
        $estado = new Estado();
        $estado->setNombre("Privado");
        $tarea->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new TareaVoter($security);
        try {
            $voter->vote($token, $tarea, [TareaVoter::OWN]);
            $this->fail("No se detectó que la tarea no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La tarea no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la tarea", $apiProblem->getUserMessage());
        }
    }
}

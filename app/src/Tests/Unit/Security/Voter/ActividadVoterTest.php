<?php

namespace App\Tests\Unit\Security\Voter;

use App\Api\ApiProblemException;
use App\Entity\Actividad;
use App\Entity\Autor;
use App\Entity\Estado;
use App\Security\Voter\ActividadVoter;
use App\Tests\Support\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ActividadVoterTest extends TestCase
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

        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Público");
        $actividad->setEstado($estado);
        $actividad->setAutor($autor);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        $result = $voter->vote($token, $actividad, [ActividadVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantAccessPublicOther()
    {
        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Público");
        $actividad->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        $result = $voter->vote($token, $actividad, [ActividadVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantOwnPublicOwned()
    {
        $autor = new Autor();

        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Público");
        $actividad->setEstado($estado);
        $actividad->setAutor($autor);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        $result = $voter->vote($token, $actividad, [ActividadVoter::OWN]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testDenyOwnPublicOther()
    {
        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Público");
        $actividad->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        try {
            $voter->vote($token, $actividad, [ActividadVoter::OWN]);
            $this->fail("No se detectó que la actividad no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La actividad no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la actividad", $apiProblem->getUserMessage());
        }
    }

    public function testGrantAccessPrivateToOwner()
    {
        $autor = new Autor();
        $actividad = new Actividad();
        $actividad->setAutor($autor);
        $estado = new Estado();
        $estado->setNombre("Privado");
        $actividad->setEstado($estado);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        $result = $voter->vote($token, $actividad, [ActividadVoter::ACCESS]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testGrantOwnPrivateToOwner()
    {
        $autor = new Autor();
        $actividad = new Actividad();
        $actividad->setAutor($autor);
        $estado = new Estado();
        $estado->setNombre("Privado");
        $actividad->setEstado($estado);

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        $result = $voter->vote($token, $actividad, [ActividadVoter::OWN]);
        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testDenyAccessPrivateOther()
    {
        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Privado");
        $actividad->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        try {
            $voter->vote($token, $actividad, [ActividadVoter::ACCESS]);
            $this->fail("No se detectó que la actividad no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La actividad es privada o no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la actividad", $apiProblem->getUserMessage());
        }
    }

    public function testDenyOwnPrivateOther()
    {
        $actividad = new Actividad();
        $estado = new Estado();
        $estado->setNombre("Privado");
        $actividad->setEstado($estado);

        $autor = new Autor();

        $token = new UsernamePasswordToken($autor, null, "test", ["ROLE_AUTOR"]);
        self::$container->get('security.token_storage')->setToken($token);

        $security = new Security(self::$container);
        $voter = new ActividadVoter($security);
        try {
            $voter->vote($token, $actividad, [ActividadVoter::OWN]);
            $this->fail("No se detectó que la actividad no pertenece al usuario");
        } catch (ApiProblemException $e) {
            $apiProblem = $e->getApiProblem();
            $this->assertEquals("La actividad no pertenece al usuario actual", $apiProblem->getDeveloperMessage());
            $this->assertEquals("No se puede acceder a la actividad", $apiProblem->getUserMessage());
        }
    }
}

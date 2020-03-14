<?php

namespace App\DataFixtures;

use App\Entity\Actividad;
use App\Entity\Salto;
use App\Entity\Tarea;
use Doctrine\Persistence\ObjectManager;

class PlanificacionFixture extends BaseFixture
{
    /** @param array $saltoArray Array of origen, condicion, destinos and maybe respuesta */
    private function createSalto(array $saltoArray, $em)
    {
        $salto = new Salto();
        $salto->setOrigen($saltoArray["origen"]);
        $salto->setCondicion($saltoArray["condicion"]);
        foreach ($saltoArray["destinos"] as $destino) {
            $salto->addDestino($destino);
        }
        $salto->setPlanificacion($saltoArray["planificacion"]);
        $salto->setRespuesta(array_key_exists("respuesta", $saltoArray) ? $saltoArray["respuesta"] : null);
        $em->persist($salto);
        $em->flush();
        return $salto;
    }

    protected function loadData(ObjectManager $manager)
    {
        $tareas = $manager->getRepository(Tarea::class)->findBy(["consigna" => "Tarea libre!"]);
        $actividad = $manager->getRepository(Actividad::class)->find(4);
        $planificacion = $actividad->getPlanificacion();
        $salto = $this->createSalto([
            "origen" => $tareas[0],
            "condicion" => "ALL",
            "destinos" => [$tareas[1]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[1],
            "condicion" => "ALL",
            "destinos" => [$tareas[2]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[2],
            "condicion" => "ALL",
            "destinos" => [$tareas[3], $tareas[5], $tareas[8]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[3],
            "condicion" => "ALL",
            "destinos" => [$tareas[4]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[5],
            "condicion" => "ALL",
            "destinos" => [$tareas[6]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[6],
            "condicion" => "ALL",
            "destinos" => [$tareas[7]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $salto = $this->createSalto([
            "origen" => $tareas[8],
            "condicion" => "ALL",
            "destinos" => [$tareas[9]],
            "planificacion" => $planificacion
        ], $manager);
        $manager->persist($salto);

        $planificacion->addInicial($tareas[2]);
        $planificacion->addInicial($tareas[5]);

        $planificacion->addOpcional($tareas[4]);
        $planificacion->addOpcional($tareas[6]);
        $planificacion->addOpcional($tareas[9]);

        $manager->persist($planificacion);

        $manager->persist($actividad);
        $manager->flush();
    }
}

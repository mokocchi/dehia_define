<?php

namespace App\DataFixtures;

use App\Entity\Actividad;
use App\Entity\ActividadTarea;
use App\Entity\Autor;
use App\Entity\Dominio;
use App\Entity\Estado;
use App\Entity\Tarea;
use App\Entity\TipoTarea;
use App\Service\UploaderHelper;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TareaFixture extends BaseFixture
{
    private static $planosTarea = [
        'mesa1.png',
        'mesa2.png',
        'suelo.png'
    ];

    private $uploaderHelper;
    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    private function fakeUploadImage(string $codigo)
    {
        $randomImage = $this->faker->randomElement(self::$planosTarea);

        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir() . '/' . $randomImage;
        $fs->copy(__DIR__ . '/images/' . $randomImage, $targetPath, true);

        $this->uploaderHelper->uploadPlano(new File($targetPath), $codigo, false);
    }

    private function addActividadTarea($actividad, $tarea, $orden)
    {
        $actividadTarea = new ActividadTarea();
        $actividadTarea->setActividad($actividad);
        $actividadTarea->setTarea($tarea);
        $actividadTarea->setOrden($orden);
        $actividad->addActividadTarea($actividadTarea);
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_tareas', function ($count) use ($manager) {
            $tarea = new Tarea();
            $tarea->setNombre("Tarea prueba" . ($count + 1))
                ->setConsigna("Tarea libre!");

            $tipoDeposito = $manager->getRepository(TipoTarea::class)->findOneBy(["codigo" => "deposit"]);
            $tarea->setTipo($tipoDeposito);

            $dominioPruebas = $manager->getRepository(Dominio::class)->findOneBy(["nombre" => "Pruebas"]);
            $tarea->setDominio($dominioPruebas);

            // publish most tareas
            $estadoRepository = $manager->getRepository(Estado::class);
            if ($this->faker->boolean(70)) {
                $publico = $estadoRepository->findOneBy(["nombre" => "Público"]);
                $tarea->setEstado($publico);
            } else {
                $privado = $estadoRepository->findOneBy(["nombre" => "Privado"]);
                $tarea->setEstado($privado);
            }

            $tarea->setCodigo($this->faker->sha256);

            $autor = $manager->getRepository(Autor::class)->findOneBy(["email" => "autor1@dehia.net"]);
            $tarea->setAutor($autor);

            $this->fakeUploadImage($tarea->getCodigo());

            return $tarea;
        });
        $manager->flush();

        $tareas = $manager->getRepository(Tarea::class)->findBy(["consigna" => "Tarea libre!"]);
        $actividad = $manager->getRepository(Actividad::class)->find(1);
        $orden = 1;
        $this->addActividadTarea($actividad, $tareas[0], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[1], $orden);
        $orden++;
        $manager->persist($actividad);
        $manager->flush();

        $actividad = $manager->getRepository(Actividad::class)->find(2);
        $this->addActividadTarea($actividad, $tareas[0], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[1], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[3], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[2], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[4], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[5], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[6], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[7], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[8], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[9], $orden);
        $orden++;
        $manager->persist($actividad);
        $manager->flush();

        $actividad = $manager->getRepository(Actividad::class)->find(4);
        $this->addActividadTarea($actividad, $tareas[0], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[1], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[3], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[2], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[4], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[5], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[6], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[7], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[8], $orden);
        $orden++;
        $this->addActividadTarea($actividad, $tareas[9], $orden);
        $orden++;

        $manager->persist($actividad);
        $manager->flush();
    }
}

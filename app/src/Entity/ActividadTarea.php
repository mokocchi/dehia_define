<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActividadTareaRepository")
 */
class ActividadTarea
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actividad", inversedBy="actividadTareas")
     */
    private $actividad;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tarea")
     */
    private $tarea;

    /**
     * @ORM\Column(type="integer")
     */
    private $orden;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActividad(): ?Actividad
    {
        return $this->actividad;
    }

    public function setActividad(?Actividad $actividad): self
    {
        $this->actividad = $actividad;

        return $this;
    }

    public function getTarea(): ?Tarea
    {
        return $this->tarea;
    }

    public function setTarea(?Tarea $tarea): self
    {
        $this->tarea = $tarea;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }
}

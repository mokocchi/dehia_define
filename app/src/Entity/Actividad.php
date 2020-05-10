<?php

namespace App\Entity;

use App\Annotation\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActividadRepository")
 * @ExclusionPolicy("all")
 * @Link(
 *  "self",
 *  route = "show_actividad",
 *  params = { "id": "object.getId() ?: 0" }
 * )
 */
class Actividad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $nombre;

    /**
     * @Expose
     * @Groups({"autor", "publico"})
     * @ORM\Column(type="string", length=255)
     */
    private $objetivo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Idioma")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $idioma;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Dominio")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $dominio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoPlanificacion")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $tipoPlanificacion;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Planificacion", cascade={"persist", "remove"})
     */
    private $planificacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Autor", inversedBy="actividadesCreadas")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $autor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Estado")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $codigo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActividadTarea", mappedBy="actividad")
     */
    private $actividadTareas;

    /**
     * @ORM\Column(type="boolean")
     * @Expose
     * @Groups({"autor"})
     */
    private $definitiva;

    /**
     * @ORM\Column(type="boolean")
     * @Expose
     * @Groups({"autor"})
     */
    private $cerrada;

    public function __construct()
    {
        $this->tareas = new ArrayCollection();
        $this->actividadTareas = new ArrayCollection();
        $this->definitiva = false;
        $this->cerrada = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getObjetivo(): ?string
    {
        return $this->objetivo;
    }

    public function setObjetivo(string $objetivo): self
    {
        $this->objetivo = $objetivo;

        return $this;
    }

    public function getIdioma(): ?Idioma
    {
        return $this->idioma;
    }

    public function setIdioma(?Idioma $idioma): self
    {
        $this->idioma = $idioma;

        return $this;
    }

    public function getDominio(): ?Dominio
    {
        return $this->dominio;
    }

    public function setDominio(?Dominio $dominio): self
    {
        $this->dominio = $dominio;

        return $this;
    }

    public function getTipoPlanificacion(): ?TipoPlanificacion
    {
        return $this->tipoPlanificacion;
    }

    public function setTipoPlanificacion(?TipoPlanificacion $tipoPlanificacion): self
    {
        $this->tipoPlanificacion = $tipoPlanificacion;

        return $this;
    }

    public function getPlanificacion(): ?Planificacion
    {
        return $this->planificacion;
    }

    public function setPlanificacion(?Planificacion $planificacion): self
    {
        $this->planificacion = $planificacion;

        return $this;
    }

    public function getAutor(): ?Autor
    {
        return $this->autor;
    }

    public function setAutor(?Autor $autor): self
    {
        $this->autor = $autor;

        return $this;
    }

    public function getEstado(): ?Estado
    {
        return $this->estado;
    }

    public function setEstado(?Estado $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * @return Collection|ActividadTarea[]
     */
    public function getActividadTareas(): Collection
    {
        return $this->actividadTareas;
    }

    public function addActividadTarea(ActividadTarea $actividadTarea): self
    {
        if (!$this->actividadTareas->contains($actividadTarea)) {
            $this->actividadTareas[] = $actividadTarea;
            $actividadTarea->setActividad($this);
        }

        return $this;
    }

    public function removeActividadTarea(ActividadTarea $actividadTarea): self
    {
        if ($this->actividadTareas->contains($actividadTarea)) {
            $this->actividadTareas->removeElement($actividadTarea);
            // set the owning side to null (unless already changed)
            if ($actividadTarea->getActividad() === $this) {
                $actividadTarea->setActividad(null);
            }
        }

        return $this;
    }

    public function getDefinitiva(): ?bool
    {
        return $this->definitiva;
    }

    public function setDefinitiva(bool $definitiva): self
    {
        $this->definitiva = $definitiva;

        return $this;
    }

    public function getCerrada(): ?bool
    {
        return $this->cerrada;
    }

    public function setCerrada(bool $cerrada): self
    {
        $this->cerrada = $cerrada;

        return $this;
    }
}

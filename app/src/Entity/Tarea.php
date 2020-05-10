<?php

namespace App\Entity;

use App\Annotation\Link;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TareaRepository")
 * @ExclusionPolicy("all")
 * @Link(
 *  "self",
 *  route = "show_tarea",
 *  params = { "id": "object.getId()" }
 * )
 */
class Tarea
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
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $consigna;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Dominio")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $dominio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoTarea")
     * @ORM\JoinColumn(nullable=true)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $tipo;

    /**
     * @ORM\Column(type="json", nullable=false)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $extra = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $codigo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Autor", inversedBy="tareas")
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
     * @Expose
     * @Groups({"autor", "publico"})
     */
    private $orden;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getConsigna(): ?string
    {
        return $this->consigna;
    }

    public function setConsigna(string $consigna): self
    {
        $this->consigna = $consigna;

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

    public function getTipo(): ?TipoTarea
    {
        return $this->tipo;
    }

    public function setTipo(?TipoTarea $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function setExtra(?array $extra): self
    {
        $this->extra = $extra;

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

    public function getPlanoPath(): string
    {
        return UploaderHelper::PLANOS . '/' . $this->getCodigo();
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

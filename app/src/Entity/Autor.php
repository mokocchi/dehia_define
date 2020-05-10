<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AutorRepository")
 * @ExclusionPolicy("all")
 */
class Autor implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"auth", "autor", "publico"})
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"auth", "autor", "publico"})
     */
    private $apellido;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"auth"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Groups({"auth", "autor"})
     */
    private $googleid;

    /**
     * @Expose
     * @Groups({"auth"})
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Actividad", mappedBy="autor")
     */
    private $actividadesCreadas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tarea", mappedBy="autor")
     */
    private $tareas;

    private $oauth;

    private $token;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->actividadesCreadas = new ArrayCollection();
        $this->tareas = new ArrayCollection();
    }

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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getGoogleid(): ?string
    {
        return $this->googleid;
    }

    public function setGoogleid(string $googleid): self
    {
        $this->googleid = $googleid;

        return $this;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        foreach ($this->roles as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    public function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }

    /**
     * @return Collection|Actividad[]
     */
    public function getActividadesCreadas(): Collection
    {
        return $this->actividadesCreadas;
    }

    public function addActividadesCreada(Actividad $actividadesCreada): self
    {
        if (!$this->actividadesCreadas->contains($actividadesCreada)) {
            $this->actividadesCreadas[] = $actividadesCreada;
            $actividadesCreada->setAutor($this);
        }

        return $this;
    }

    public function removeActividadesCreada(Actividad $actividadesCreada): self
    {
        if ($this->actividadesCreadas->contains($actividadesCreada)) {
            $this->actividadesCreadas->removeElement($actividadesCreada);
            // set the owning side to null (unless already changed)
            if ($actividadesCreada->getAutor() === $this) {
                $actividadesCreada->setAutor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tarea[]
     */
    public function getTareas(): Collection
    {
        return $this->tareas;
    }

    public function addTarea(Tarea $tarea): self
    {
        if (!$this->tareas->contains($tarea)) {
            $this->tareas[] = $tarea;
            $tarea->setAutor($this);
        }

        return $this;
    }

    public function removeTarea(Tarea $tarea): self
    {
        if ($this->tareas->contains($tarea)) {
            $this->tareas->removeElement($tarea);
            // set the owning side to null (unless already changed)
            if ($tarea->getAutor() === $this) {
                $tarea->setAutor(null);
            }
        }

        return $this;
    }

    public function getOauth(): bool
    {
        return $this->oauth;
    }

    public function setOauth(bool $oauth): self
    {
        $this->oauth = $oauth;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }
}

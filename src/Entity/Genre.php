<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, JV>
     */
    #[ORM\OneToMany(mappedBy: 'genre', targetEntity: JV::class)]
    private Collection $jvs;

    public function __construct()
    {
        $this->jvs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, JV>
     */
    public function getJvs(): Collection
    {
        return $this->jvs;
    }

    public function addJv(JV $jv): static
    {
        if (!$this->jvs->contains($jv)) {
            $this->jvs->add($jv);
            $jv->setGenre($this);
        }

        return $this;
    }

    public function removeJv(JV $jv): static
    {
        if ($this->jvs->removeElement($jv)) {
            if ($jv->getGenre() === $this) {
                $jv->setGenre(null);
            }
        }

        return $this;
    }
}
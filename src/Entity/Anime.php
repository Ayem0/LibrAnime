<?php

namespace App\Entity;

use App\Repository\AnimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimeRepository::class)]
#[AsEntityAutocompleteField]
class Anime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'animes')]
    private Collection $categorie;

    #[ORM\ManyToMany(targetEntity: Liste::class, mappedBy: 'anime')]
    private Collection $listes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $mal_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trailerUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trailerImg = null;

    #[ORM\Column(nullable: true)]
    private ?int $episodes = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    private ?int $averageScore = null;

    #[ORM\ManyToOne(inversedBy: 'animes')]
    private ?Format $format = null;

    #[ORM\ManyToOne(inversedBy: 'animes')]
    private ?Season $season = null;

    #[ORM\ManyToOne(inversedBy: 'animes')]
    private ?Status $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $popularityScore = null;

    #[ORM\Column(nullable: true)]
    private ?int $trendingScore = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;


    public function __construct()
    {
        $this->categorie = new ArrayCollection();
        $this->listes = new ArrayCollection();

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
     * @return Collection<int, Categorie>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categorie->removeElement($categorie);

        return $this;
    }

    /**
     * @return Collection<int, Liste>
     */
    public function getListes(): Collection
    {
        return $this->listes;
    }

    public function addListe(Liste $liste): static
    {
        if (!$this->listes->contains($liste)) {
            $this->listes->add($liste);
            $liste->addAnime($this);
        }

        return $this;
    }

    public function removeListe(Liste $liste): static
    {
        if ($this->listes->removeElement($liste)) {
            $liste->removeAnime($this);
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getMalId(): ?int
    {
        return $this->mal_id;
    }

    public function setMalId(int $mal_id): static
    {
        $this->mal_id = $mal_id;

        return $this;
    }

    public function getTrailerUrl(): ?string
    {
        return $this->trailerUrl;
    }

    public function setTrailerUrl(?string $trailerUrl): static
    {
        $this->trailerUrl = $trailerUrl;

        return $this;
    }

    public function getTrailerImg(): ?string
    {
        return $this->trailerImg;
    }

    public function setTrailerImg(?string $trailerImg): static
    {
        $this->trailerImg = $trailerImg;

        return $this;
    }

    public function getEpisodes(): ?int
    {
        return $this->episodes;
    }

    public function setEpisodes(?int $episodes): static
    {
        $this->episodes = $episodes;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getAverageScore(): ?int
    {
        return $this->averageScore;
    }

    public function setAverageScore(?int $averageScore): static
    {
        $this->averageScore = $averageScore;

        return $this;
    }

    public function getFormat(): ?Format
    {
        return $this->format;
    }

    public function setFormat(?Format $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPopularityScore(): ?int
    {
        return $this->popularityScore;
    }

    public function setPopularityScore(?int $popularityScore): static
    {
        $this->popularityScore = $popularityScore;

        return $this;
    }

    public function getTrendingScore(): ?int
    {
        return $this->trendingScore;
    }

    public function setTrendingScore(?int $trendingScore): static
    {
        $this->trendingScore = $trendingScore;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
    
}

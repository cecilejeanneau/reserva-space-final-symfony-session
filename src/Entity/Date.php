<?php

namespace App\Entity;

use App\Repository\DateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateRepository::class)]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?bool $morning = null;

    #[ORM\Column(nullable: true)]
    private ?bool $afternoon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isMorning(): ?bool
    {
        return $this->morning;
    }

    public function setMorning(?bool $morning): static
    {
        $this->morning = $morning;

        return $this;
    }

    public function isAfternoon(): ?bool
    {
        return $this->afternoon;
    }

    public function setAfternoon(?bool $afternoon): static
    {
        $this->afternoon = $afternoon;

        return $this;
    }
}

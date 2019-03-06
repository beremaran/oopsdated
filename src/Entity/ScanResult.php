<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScanResultRepository")
 */
class ScanResult
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\GithubRepo", inversedBy="scanResult", cascade={"persist", "remove"})
     */
    private $repository;

    /**
     * @ORM\Column(type="array")
     */
    private $jsonReport = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepository(): ?GithubRepo
    {
        return $this->repository;
    }

    public function setRepository(?GithubRepo $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getJsonReport(): ?array
    {
        return $this->jsonReport;
    }

    public function setJsonReport(array $jsonReport): self
    {
        $this->jsonReport = $jsonReport;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

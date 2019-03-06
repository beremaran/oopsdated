<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationFileRepository")
 */
class ConfigurationFile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GithubRepo", inversedBy="configurationFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $repository;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $packageManagerType;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

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

    public function getPackageManagerType(): ?string
    {
        return $this->packageManagerType;
    }

    public function setPackageManagerType(string $packageManagerType): self
    {
        $this->packageManagerType = $packageManagerType;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}

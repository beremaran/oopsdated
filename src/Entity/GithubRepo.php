<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GithubRepoRepository")
 */
class GithubRepo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EmailAddress", inversedBy="githubRepos")
     */
    private $subscribedEmails;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConfigurationFile", mappedBy="repository", orphanRemoval=true)
     */
    private $configurationFiles;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ScanResult", mappedBy="repository", cascade={"persist", "remove"})
     */
    private $scanResult;

    public function __construct()
    {
        $this->subscribedEmails = new ArrayCollection();
        $this->configurationFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|EmailAddress[]
     */
    public function getSubscribedEmails(): Collection
    {
        return $this->subscribedEmails;
    }

    public function addSubscribedEmail(EmailAddress $subscribedEmail): self
    {
        if (!$this->subscribedEmails->contains($subscribedEmail)) {
            $this->subscribedEmails[] = $subscribedEmail;
        }

        return $this;
    }

    public function removeSubscribedEmail(EmailAddress $subscribedEmail): self
    {
        if ($this->subscribedEmails->contains($subscribedEmail)) {
            $this->subscribedEmails->removeElement($subscribedEmail);
        }

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

    /**
     * @return Collection|ConfigurationFile[]
     */
    public function getConfigurationFiles(): Collection
    {
        return $this->configurationFiles;
    }

    public function addConfigurationFile(ConfigurationFile $configurationFile): self
    {
        if (!$this->configurationFiles->contains($configurationFile)) {
            $this->configurationFiles[] = $configurationFile;
            $configurationFile->setRepository($this);
        }

        return $this;
    }

    public function removeConfigurationFile(ConfigurationFile $configurationFile): self
    {
        if ($this->configurationFiles->contains($configurationFile)) {
            $this->configurationFiles->removeElement($configurationFile);
            // set the owning side to null (unless already changed)
            if ($configurationFile->getRepository() === $this) {
                $configurationFile->setRepository(null);
            }
        }

        return $this;
    }

    public function getScanResult(): ?ScanResult
    {
        return $this->scanResult;
    }

    public function setScanResult(?ScanResult $scanResult): self
    {
        $this->scanResult = $scanResult;

        // set (or unset) the owning side of the relation if necessary
        $newRepository = $scanResult === null ? null : $this;
        if ($newRepository !== $scanResult->getRepository()) {
            $scanResult->setRepository($newRepository);
        }

        return $this;
    }
}

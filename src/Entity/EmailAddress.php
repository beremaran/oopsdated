<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailAddressRepository")
 */
class EmailAddress
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
    private $email;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\GithubRepo", mappedBy="subscribedEmails")
     */
    private $githubRepos;

    public function __construct()
    {
        $this->githubRepos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|GithubRepo[]
     */
    public function getGithubRepos(): Collection
    {
        return $this->githubRepos;
    }

    public function addGithubRepo(GithubRepo $githubRepo): self
    {
        if (!$this->githubRepos->contains($githubRepo)) {
            $this->githubRepos[] = $githubRepo;
            $githubRepo->addSubscribedEmail($this);
        }

        return $this;
    }

    public function removeGithubRepo(GithubRepo $githubRepo): self
    {
        if ($this->githubRepos->contains($githubRepo)) {
            $this->githubRepos->removeElement($githubRepo);
            $githubRepo->removeSubscribedEmail($this);
        }

        return $this;
    }
}

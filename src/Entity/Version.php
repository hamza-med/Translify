<?php

namespace App\Entity;

use App\Repository\VersionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VersionRepository::class)
 */
class Version
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $versionNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="versions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity=Language::class, inversedBy="versions",cascade={"persist","remove"})
     */
    private $language;

    /**
     * @ORM\OneToOne(targetEntity=Translation::class, mappedBy="version", cascade={"persist", "remove"})
     */
    private $translation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersionNumber(): ?float
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(float $versionNumber): self
    {
        $this->versionNumber = $versionNumber;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getTranslation(): ?Translation
    {
        return $this->translation;
    }

    public function setTranslation(?Translation $translation): self
    {
        // unset the owning side of the relation if necessary
        if ($translation === null && $this->translation !== null) {
            $this->translation->setVersion(null);
        }

        // set the owning side of the relation if necessary
        if ($translation !== null && $translation->getVersion() !== $this) {
            $translation->setVersion($this);
        }

        $this->translation = $translation;

        return $this;
    }
}

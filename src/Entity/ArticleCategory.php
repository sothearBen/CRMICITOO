<?php

namespace App\Entity;

use App\Repository\ArticleCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @ORM\Entity(repositoryClass=ArticleCategoryRepository::class)
 */
class ArticleCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity=Article::class, mappedBy="categories", cascade={"persist", "remove"})
     */
    private $articles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayedMenu;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayedHome;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->displayedHome = false;
        $this->displayedMenu = false;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->addCategory($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            $article->removeCategory($this);
        }

        return $this;
    }

    public function getDisplayedMenu(): ?bool
    {
        return $this->displayedMenu;
    }

    public function setDisplayedMenu(bool $displayedMenu): self
    {
        $this->displayedMenu = $displayedMenu;

        return $this;
    }

    public function getDisplayedHome(): ?bool
    {
        return $this->displayedHome;
    }

    public function setDisplayedHome(bool $displayedHome): self
    {
        $this->displayedHome = $displayedHome;

        return $this;
    }
}
<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_categories")
 */
class Category
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \App\Entity\Blog
     * @ORM\ManyToOne(targetEntity="Blog")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Post", mappedBy="categories")
     */
    protected $posts;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $url;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Blog
     */
    public function getBlog(): Blog
    {
        return $this->blog;
    }

    /**
     * @param \App\Entity\Blog $blog
     *
     * @return $this
     */
    public function setBlog(Blog $blog): self
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @param \App\Entity\Post $post
     */
    public function addPost(Post $post): void
    {
        if ($this->posts->contains($post)) {
            return;
        }
        $this->posts->add($post);
        $post->addCategory($this);
    }

    /**
     * @param \App\Entity\Post $post
     */
    public function removePost(Post $post): void
    {
        if (!$this->posts->contains($post)) {
            return;
        }
        $this->posts->removeElement($post);
        $post->removeCategory($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}

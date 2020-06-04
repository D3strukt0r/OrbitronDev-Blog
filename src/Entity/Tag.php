<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_tags")
 */
class Tag
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Blog
     * @ORM\ManyToOne(targetEntity="Blog")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Post", mappedBy="tags")
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
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Blog The blog
     */
    public function getBlog(): Blog
    {
        return $this->blog;
    }

    /**
     * @param Blog $blog The blog
     *
     * @return $this
     */
    public function setBlog(Blog $blog): self
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @param Post $post The post
     */
    public function addPost(Post $post): void
    {
        if ($this->posts->contains($post)) {
            return;
        }
        $this->posts->add($post);
        $post->addTag($this);
    }

    /**
     * @param Post $post The post
     */
    public function removePost(Post $post): void
    {
        if (!$this->posts->contains($post)) {
            return;
        }
        $this->posts->removeElement($post);
        $post->addTag($this);
    }

    /**
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name The name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string The url
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url The url
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}

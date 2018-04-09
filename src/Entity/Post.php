<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_posts")
 */
class Post
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
     * @ORM\ManyToOne(targetEntity="Blog", inversedBy="posts")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    protected $author;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var null|string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $published_on;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="posts")
     * @ORM\JoinTable(name="blog_m2m_post_categories",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    protected $categories;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="posts")
     * @ORM\JoinTable(name="blog_m2m_post_tags",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

    /**
     * @var null|string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $header_image;

    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $story;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $comments;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
     * @return \App\Entity\User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param \App\Entity\User $author
     *
     * @return $this
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return $this
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedOn(): \DateTime
    {
        return $this->published_on;
    }

    /**
     * @param \DateTime $published_on
     *
     * @return $this
     */
    public function setPublishedOn(\DateTime $published_on): self
    {
        $this->published_on = $published_on;

        return $this;
    }

    /**
     * @return \App\Entity\Category[]
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    /**
     * @param \App\Entity\Category $category
     */
    public function addCategory(Category $category): void
    {
        if ($this->categories->contains($category)) {
            return;
        }
        $this->categories->add($category);
        $category->addPost($this);
    }

    /**
     * @param \App\Entity\Category $category
     */
    public function removeCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            return;
        }
        $this->categories->removeElement($category);
        $category->removePost($this);
    }

    /**
     * @param \App\Entity\Tag $tag
     */
    public function addTag(Tag $tag): void
    {
        if ($this->tags->contains($tag)) {
            return;
        }
        $this->tags->add($tag);
        $tag->addPost($this);
    }

    /**
     * @param \App\Entity\Tag $tag
     */
    public function removeTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            return;
        }
        $this->tags->removeElement($tag);
        $tag->removePost($this);
    }

    /**
     * @return null|string
     */
    public function getHeaderImage(): ?string
    {
        return $this->header_image;
    }

    /**
     * @param null|string $header_image
     *
     * @return $this
     */
    public function setHeaderImage(string $header_image = null): self
    {
        $this->header_image = $header_image;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStory(): ?string
    {
        return $this->story;
    }

    /**
     * @param null|string $story
     *
     * @return $this
     */
    public function setStory(string $story = null): self
    {
        $this->story = $story;

        return $this;
    }

    /**
     * @return \App\Entity\Comment[]
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    /**
     * @param \App\Entity\Comment $comment
     *
     * @return $this
     */
    public function addComment(Comment $comment): self
    {
        $this->comments->add($comment);
        $comment->setPost($this);

        return $this;
    }

    /**
     * @param \App\Entity\Comment $comment
     *
     * @return $this
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
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
     * @var Blog
     * @ORM\ManyToOne(targetEntity="Blog", inversedBy="posts")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)
     */
    protected $blog;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $summary;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $publishedAt;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    protected $author;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="Comment",
     *     mappedBy="post",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"})
     * @ORM\OrderBy({"publishedAt": "DESC"})
     */
    protected $comments;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="posts")
     * @ORM\JoinTable(name="blog_m2m_post_categories",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    protected $categories;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="posts")
     * @ORM\JoinTable(name="blog_m2m_post_tags",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $header_image;

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
     * @return Blog
     */
    public function getBlog(): Blog
    {
        return $this->blog;
    }

    /**
     * @param Blog $blog
     *
     * @return $this
     */
    public function setBlog(Blog $blog): self
    {
        $this->blog = $blog;

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
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPublishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param DateTime $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt(DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     *
     * @return $this
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    /**
     * @param Comment $comment
     *
     * @return $this
     */
    public function addComment(Comment $comment): self
    {
        $comment->setPost($this);
        $this->comments->add($comment);

        return $this;
    }

    /**
     * @param Comment $comment
     *
     * @return $this
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $comment->setPost(null);
            $this->comments->removeElement($comment);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    /**
     * @param Category $category
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
     * @param Category $category
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
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags->toArray();
    }

    /**
     * @param Tag $tag
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
     * @param Tag $tag
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
     * @return string|null
     */
    public function getHeaderImage(): ?string
    {
        return $this->header_image;
    }

    /**
     * @param string|null $header_image
     *
     * @return $this
     */
    public function setHeaderImage(string $header_image = null): self
    {
        $this->header_image = $header_image;

        return $this;
    }
}

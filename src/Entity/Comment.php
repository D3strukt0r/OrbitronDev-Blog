<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blog_comments")
 */
class Comment
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    protected $post;

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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $author;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $responses;

    /**
     * @var Comment|null
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="responses")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    public function __construct()
    {
        $this->publishedAt = new DateTime();
        $this->responses = new ArrayCollection();
    }

    /**
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Post The post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post The post
     *
     * @return $this
     */
    public function setPost(Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return string The content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content The content
     *
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return DateTime The publishing time
     */
    public function getPublishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param DateTime $publishedAt The publishing time
     *
     * @return $this
     */
    public function setPublishedAt(DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return User The user
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author The user
     *
     * @return $this
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Comment[] The comments
     */
    public function getResponses(): array
    {
        return $this->responses->toArray();
    }

    /**
     * @param Comment $comment The comment
     *
     * @return $this
     */
    public function addResponse(self $comment): self
    {
        $this->responses->add($comment);
        $comment->setPost($this->post);
        $comment->setParent($this);

        return $this;
    }

    /**
     * @param Comment $comment The comment
     *
     * @return $this
     */
    public function removeResponse(self $comment): self
    {
        if ($this->responses->contains($comment)) {
            $this->responses->removeElement($comment);
        }

        return $this;
    }

    /**
     * @return Comment|null The parent comment, or null if it's the top most comment
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Comment|null $parent The parent comment, or null if it's the top most comment
     *
     * @return $this
     */
    public function setParent(self $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }
}

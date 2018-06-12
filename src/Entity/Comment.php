<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var \App\Entity\Post
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
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $publishedAt;

    /**
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $author;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $responses;

    /**
     * @var null|\App\Entity\Comment
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="responses")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->responses = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param \App\Entity\Post $post
     *
     * @return $this
     */
    public function setPost(Post $post): self
    {
        $this->post = $post;

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
     * @return \DateTime
     */
    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt(\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

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
     * @return \App\Entity\Comment[]
     */
    public function getResponses(): array
    {
        return $this->responses->toArray();
    }

    /**
     * @param \App\Entity\Comment $comment
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
     * @param \App\Entity\Comment $comment
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
     * @return null|\App\Entity\Comment
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param null|\App\Entity\Comment $parent
     *
     * @return $this
     */
    public function setParent(self $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }
}

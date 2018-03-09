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
     * @var \App\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $comment;

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

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date;

    public function __construct()
    {
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
     * @return \App\Entity\User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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
    public function addResponse(Comment $comment): self
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
    public function removeResponse(Comment $comment): self
    {
        if ($this->responses->contains($comment)) {
            $this->responses->removeElement($comment);
        }

        return $this;
    }

    /**
     * @return null|\App\Entity\Comment
     */
    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    /**
     * @param null|\App\Entity\Comment $parent
     *
     * @return $this
     */
    public function setParent(Comment $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }
}

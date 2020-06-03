<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SessionRepository::class)
 * @ORM\Table(name="sessions", options={"collate": "utf8mb4_bin"})
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(type="binary")
     */
    private $sess_id;

    /**
     * @ORM\Column(type="blob")
     */
    private $sess_data;

    /**
     * @ORM\Column(type="bigint")
     */
    private $sess_lifetime;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $sess_time;

    public function getSessId()
    {
        return $this->sess_id;
    }

    public function setSessId($sess_id): self
    {
        $this->sess_id = $sess_id;

        return $this;
    }

    public function getSessData()
    {
        return $this->sess_data;
    }

    public function setSessData($sess_data): self
    {
        $this->sess_data = $sess_data;

        return $this;
    }

    public function getSessLifetime(): ?string
    {
        return $this->sess_lifetime;
    }

    public function setSessLifetime(string $sess_lifetime): self
    {
        $this->sess_lifetime = $sess_lifetime;

        return $this;
    }

    public function getSessTime(): ?int
    {
        return $this->sess_time;
    }

    public function setSessTime(int $sess_time): self
    {
        $this->sess_time = $sess_time;

        return $this;
    }
}

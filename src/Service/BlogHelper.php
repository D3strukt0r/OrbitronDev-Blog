<?php

namespace App\Service;

use App\Entity\Blog;
use Doctrine\Common\Persistence\ObjectManager;

class BlogHelper
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager $em
     */
    private $em;

    public function __construct(ObjectManager $manager)
    {
        $this->em = $manager;
    }

    /**
     * Checks whether the given url exists, in other words, if the blog exists
     *
     * @param string $url
     *
     * @return bool
     */
    public function urlExists($url)
    {
        /** @var null|\App\Entity\Blog[] $find */
        $find = $this->em->getRepository(Blog::class)->findBy(['url' => $url]);

        if (!is_null($find)) {
            if (count($find)) {
                return true;
            }
        }
        return false;
    }
}

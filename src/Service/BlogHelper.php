<?php

namespace App\Service;

use App\Entity\Blog;
use Doctrine\Common\Persistence\ManagerRegistry;

class BlogHelper
{
    public static $settings = [
        'blog' => [
            'name' => [
                'min_length' => 4,
            ],
            'url' => [
                'min_length' => 3,
            ],
        ],
    ];

    /**
     * @var ManagerRegistry
     */
    private $em;

    public function __construct(ManagerRegistry $manager)
    {
        $this->em = $manager;
    }

    /**
     * Checks whether the given url exists, in other words, if the blog exists.
     *
     * @param string $url
     *
     * @return bool
     */
    public function urlExists($url)
    {
        /** @var Blog[]|null $find */
        $find = $this->em->getRepository(Blog::class)->findBy(['url' => $url]);

        if (null !== $find) {
            if (count($find)) {
                return true;
            }
        }

        return false;
    }
}

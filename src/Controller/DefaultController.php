<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Post;
use App\Form\NewBlogType;
use App\Service\BlogHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends Controller
{
    public function index(ObjectManager $em)
    {
        /** @var \App\Entity\Blog[] $blogList */
        $blogList = $em->getRepository(Blog::class)->findAll();

        return $this->render('list-blogs.html.twig', [
            'blog_list' => $blogList,
        ]);
    }

    public function newBlog(ObjectManager $em, Request $request, BlogHelper $helper)
    {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        //////////// END TEST IF USER IS LOGGED IN ////////////

        $createBlogForm = $this->createForm(NewBlogType::class);
        $createBlogForm->handleRequest($request);
        if ($createBlogForm->isSubmitted() && $createBlogForm->isValid()) {
            $errorMessages = [];
            if (strlen($blogName = trim($createBlogForm->get('name')->getData())) == 0) {
                $errorMessages[] = '';
                $createBlogForm->get('name')->addError(new FormError('Please give your blog a name'));
            } elseif (strlen($blogName) < 4) {
                $errorMessages[] = '';
                $createBlogForm->get('name')->addError(new FormError('Your blog must have minimally 4 characters'));
            }
            if (strlen($blogUrl = trim($createBlogForm->get('url')->getData())) == 0) {
                $errorMessages[] = '';
                $createBlogForm->get('url')->addError(new FormError('Please give your blog an unique url to access it'));
            } elseif (strlen($blogUrl) < 3) {
                $errorMessages[] = '';
                $createBlogForm->get('url')->addError(new FormError('Your blog url must have minimally 3 characters'));
            } elseif (preg_match('/[^a-z_\-0-9]/i', $blogUrl)) {
                $errorMessages[] = '';
                $createBlogForm->get('url')->addError(new FormError('Only use a-z, A-Z, 0-9, _, -'));
            } elseif (in_array($blogUrl, ['new-forum', 'admin'])) {
                $errorMessages[] = '';
                $createBlogForm->get('url')->addError(new FormError('It\'s prohibited to use this url'));
            } elseif ($helper->urlExists($blogUrl)) {
                $errorMessages[] = '';
                $createBlogForm->get('url')->addError(new FormError('This url is already in use'));
            }

            if (!count($errorMessages)) {
                try {
                    $newBlog = new Blog();
                    $newBlog
                        ->setName($blogName)
                        ->setUrl($blogUrl)
                        ->setOwner($user)
                        ->setCreated(new \DateTime())
                        ->setClosed(false);
                    $em->persist($newBlog);
                    $em->flush();

                    return $this->redirectToRoute('blog_index', ['blog' => $newBlog->getUrl()]);
                } catch (\Exception $e) {
                    $createBlogForm->addError(new FormError('We could not create your blog. ('.$e->getMessage().')'));
                }
            }
        }

        return $this->render('create-new-blog.html.twig', [
            'create_blog_form' => $createBlogForm->createView(),
        ]);
    }

    public function blogIndex(ObjectManager $em, Request $request, $blog)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (is_null($blog)) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        // Get all posts
        $pagination = [];
        $pagination['item_limit'] = !is_null($request->query->get('show')) ? (int)$request->query->get('show') : 5;
        $pagination['current_page'] = !is_null($request->query->get('page')) ? (int)$request->query->get('page') : 1;

        /** @var \App\Entity\Post[] $posts */
        $posts = $em->getRepository(Post::class)->findBy(
            ['blog' => $blog],
            ['published_on' => 'DESC'],
            $pagination['item_limit'],
            ($pagination['current_page'] - 1) * $pagination['item_limit']
        );

        // Pagination
        // Reference: http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
        /** @var \App\Entity\Post[] $getPostCount */
        $getPostCount = $em->getRepository(Post::class)->findBy(['blog' => $blog]);
        $pagination['total_items'] = count($getPostCount);
        $pagination['adjacents'] = 1;

        $pagination['next_page'] = $pagination['current_page'] + 1;
        $pagination['previous_page'] = $pagination['current_page'] - 1;
        $pagination['pages_count'] = ceil($pagination['total_items'] / $pagination['item_limit']);
        $pagination['last_page_m1'] = $pagination['pages_count'] - 1;

        return $this->render('theme1/index.html.twig', [
            'current_blog' => $blog,
            'posts'        => $posts,
            'pagination'   => $pagination,
        ]);
    }

    public function blogPost(ObjectManager $em, $blog, $post)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (is_null($blog)) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        //////////// TEST IF POST EXISTS ////////////
        /** @var \App\Entity\Post $post */
        $post = $em->find(Post::class, $post);
        if (is_null($post)) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF POST EXISTS ////////////

        return $this->render('theme1/post.html.twig', [
            'current_blog' => $blog,
            'current_post' => $post,
        ]);
    }

    public function blogWritePost()
    {
        throw $this->createNotFoundException('Write Post (Coming Soon)');
    }

    public function blogWriteComment()
    {
        throw $this->createNotFoundException('Write Comment (Coming Soon)');
    }

    public function blogSearch(ObjectManager $em, $blog)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (is_null($blog)) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        return $this->render('theme1/search.html.twig', [
            'current_blog' => $blog,
        ]);
    }

    public function blogRss(ObjectManager $em, $blog)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (is_null($blog)) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        $feed = new Feed();
        $channel = new Channel();
        $channel
            ->title($blog->getName())
            ->url($this->generateUrl('blog_index', ['blog' => $blog->getUrl()], UrlGeneratorInterface::ABSOLUTE_URL))
            ->description($blog->getDescription())
            ->language($blog->getLanguage())
            ->copyright($blog->getCopyright())
            ->pubDate($blog->getCreated()->getTimestamp())
            ->lastBuildDate($blog->getCreated()->getTimestamp())
            ->ttl(60)
            ->appendTo($feed);

        /** @var \App\Entity\Post[] $postList */
        $postList = $em->getRepository(Post::class)->findBy(['blog' => $blog]);
        foreach ($postList as $post) {
            $item = new Item();
            $item
                ->title($post->getTitle())
                ->url($this->generateUrl('blog_post', ['blog' => $blog->getUrl(), 'post' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
                ->description('<div>'.$post->getDescription().'</div>')
                ->guid($this->generateUrl('blog_post', ['blog' => $blog->getUrl(), 'post' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL), true)
                ->pubDate($blog->getCreated()->getTimestamp())
                ->appendTo($channel);
        }

        header('Content-Type: application/rss+xml; charset=utf-8');

        return $feed->render();
    }

    public function blogAdmin()
    {
        throw $this->createNotFoundException('Admin (Coming Soon)');
    }
}

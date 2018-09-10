<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\NewBlogType;
use App\Service\AdminControlPanel;
use Doctrine\Common\Persistence\ObjectManager;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    public function newBlog(ObjectManager $em, Request $request, TranslatorInterface $translator)
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
            $formData = $createBlogForm->getData();

            try {
                $newBlog = new Blog();
                $newBlog
                    ->setName($formData['name'])
                    ->setUrl($formData['url'])
                    ->setOwner($user)
                    ->setCreated(new \DateTime())
                    ->setClosed(false);
                $em->persist($newBlog);
                $em->flush();

                return $this->redirectToRoute('blog_index', ['blog' => $newBlog->getUrl()]);
            } catch (\Exception $e) {
                $createBlogForm->addError(new FormError($translator->trans('new_blog.not_created', ['%error_message%' => $e->getMessage()], 'validators')));
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
        if (null === $blog) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        // Get all posts
        $pagination = [];
        $pagination['item_limit'] = $request->query->getInt('show', 5);
        $pagination['current_page'] = $request->query->getInt('page', 1);

        /** @var \App\Entity\Post[] $posts */
        $posts = $em->getRepository(Post::class)->findBy(
            ['blog' => $blog],
            ['publishedAt' => 'DESC'],
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
            'posts' => $posts,
            'pagination' => $pagination,
        ]);
    }

    public function blogPost(ObjectManager $em, $blog, $post)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (null === $blog) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        //////////// TEST IF POST EXISTS ////////////
        /** @var \App\Entity\Post $post */
        $post = $em->find(Post::class, $post);
        if (null === $post) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF POST EXISTS ////////////

        return $this->render('theme1/post.html.twig', [
            'current_blog' => $blog,
            'current_post' => $post,
        ]);
    }

    public function commentForm(Blog $blog, Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('theme1/_comment_form.html.twig', [
            'blog' => $blog,
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function blogWritePost()
    {
        throw $this->createNotFoundException('Write Post (Coming Soon)');
    }

    public function blogWriteComment(Request $request, $post, EventDispatcherInterface $eventDispatcher)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->findOneBy(['id' => $post]);

        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $comment = new Comment();
            $comment
                ->setPost($post)
                ->setAuthor($this->getUser())
                ->setContent($form->get('content')->getData());
            $post->addComment($comment);
            $em->persist($comment);
            $em->flush();

            // When triggering an event, you can optionally pass some information.
            // For simple applications, use the GenericEvent object provided by Symfony
            // to pass some PHP variables. For more complex applications, define your
            // own event object classes.
            // See https://symfony.com/doc/current/components/event_dispatcher/generic_event.html
            $event = new GenericEvent($comment);

            // When an event is dispatched, Symfony notifies it to all the listeners
            // and subscribers registered to it. Listeners can modify the information
            // passed in the event and they can even modify the execution flow, so
            // there's no guarantee that the rest of this controller will be executed.
            // See https://symfony.com/doc/current/components/event_dispatcher.html
            $eventDispatcher->dispatch('comment.created', $event);

            return $this->redirectToRoute('blog_post', ['blog' => $post->getBlog()->getUrl(), 'post' => $post->getId()]);
        }

        return $this->render('theme1/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function blogSearch(ObjectManager $em, Request $request, $blog)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (null === $blog) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF BLOG EXISTS ////////////

        if (!$request->isXmlHttpRequest()) {
            return $this->render('theme1/search.html.twig', [
                'current_blog' => $blog,
            ]);
        }

        $posts = $this->getDoctrine()->getManager()->getRepository(Post::class);
        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);
        $foundPosts = $posts->findBySearchQuery($query, $limit);

        $results = [];
        foreach ($foundPosts as $post) {
            $results[] = [
                'title' => htmlspecialchars($post->getTitle(), ENT_COMPAT | ENT_HTML5),
                'date' => $post->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($post->getAuthor()->getUsername(), ENT_COMPAT | ENT_HTML5),
                'summary' => htmlspecialchars($post->getSummary(), ENT_COMPAT | ENT_HTML5),
                'url' => $this->generateUrl('blog_post', ['blog' => $blog->getUrl(), 'post' => $post->getId()]),
            ];
        }

        return $this->json($results);
    }

    public function blogRss(ObjectManager $em, $blog)
    {
        //////////// TEST IF BLOG EXISTS ////////////
        /** @var \App\Entity\Blog $blog */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (null === $blog) {
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

    public function blogAdmin(ObjectManager $em, Request $request, $blog, $page)
    {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        //////////// END TEST IF USER IS LOGGED IN ////////////

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Blog|null $store */
        $blog = $em->getRepository(Blog::class)->findOneBy(['url' => $blog]);
        if (null === $blog) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if ($user->getId() !== $blog->getOwner()->getId()) {
            throw $this->createAccessDeniedException();
        }

        AdminControlPanel::loadLibs($this->get('kernel')->getProjectDir(), $this->container);

        $navigationLinks = AdminControlPanel::getTree();

        $view = 'DefaultController::notFound';

        $list = AdminControlPanel::getFlatTree();

        $key = null;
        while ($item = current($list)) {
            if (isset($item['href']) && $item['href'] === $page) {
                $key = key($list);
            }
            next($list);
        }

        if (null !== $key) {
            if (is_callable('\\App\\Controller\\Panel\\'.$list[$key]['view'])) {
                $view = $list[$key]['view'];
            }
        }
        $response = $this->forward('App\\Controller\\Panel\\'.$view, [
            'navigation' => $navigationLinks,
            'request' => $request,
            'blog' => $blog,
        ]);

        return $response;
    }
}

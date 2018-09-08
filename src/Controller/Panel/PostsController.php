<?php

namespace App\Controller\Panel;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostsController extends Controller
{
    public static function __setupNavigation()
    {
        return [
            [
                'type' => 'group',
                'parent' => 'root',
                'id' => 'posts',
                'title' => 'Posts',
                'href' => 'posts',
                'icon' => 'hs-admin-notepad',
                'view' => 'PostsController::index',
            ],
            [
                'type' => 'link',
                'parent' => 'posts',
                'id' => 'posts-index',
                'title' => 'Posts',
                'href' => 'posts-overview',
                'icon' => 'hs-admin-notepad',
                'view' => 'PostsController::index',
            ],
            [
                'type' => 'link',
                'parent' => 'posts',
                'id' => 'posts-new',
                'title' => 'New Post',
                'href' => 'posts-new',
                'icon' => 'hs-admin-pencil-alt',
                'view' => 'PostsController::new',
            ],
            [
                'type' => 'link',
                'parent' => 'posts',
                'id' => 'posts-edit',
                'title' => 'Edit Post',
                'href' => 'posts-edit',
                'icon' => 'hs-admin-pencil-alt',
                'view' => 'PostsController::edit',
                'display' => false,
            ],
            [
                'type' => 'link',
                'parent' => 'posts',
                'id' => 'posts-delete',
                'title' => 'Delete Post',
                'href' => 'posts-delete',
                'icon' => 'hs-admin-trash',
                'view' => 'PostsController::delete',
                'display' => false,
            ],
        ];
    }

    public static function __callNumber()
    {
        return 10;
    }

    public function index(PostRepository $posts): Response
    {
        $authorPosts = $posts->findBy(['author' => $this->getUser()], ['publishedAt' => 'DESC']);

        return $this->render('admin/blog/index.html.twig', ['posts' => $authorPosts]);
    }

    public function new(Request $request): Response
    {
        $post = new Post();
        $post->setAuthor($this->getUser());

        // See https://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'post.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('blog_admin', ['page' => 'posts-overview']);
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function show(Post $post): Response
    {
        // This security check can also be performed
        // using an annotation: @Security("is_granted('show', post)")
        $this->denyAccessUnlessGranted('show', $post, 'Posts can only be shown to their authors.');

        return $this->render('admin/blog/show.html.twig', [
            'post' => $post,
        ]);
    }

    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post, 'Posts can only be edited by their authors.');

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_post_edit', ['id' => $post->getId()]);
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('blog_admin', ['page' => 'posts-overview']);
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $post->getTags()->clear();

        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('blog_admin', ['page' => 'posts-overview']);
    }
}

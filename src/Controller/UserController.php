<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        /** @var \KnpU\OAuth2ClientBundle\Security\User\OAuthUser $user */
        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            return $this->redirectToRoute('index');
        }

        return $this->get('oauth2.registry')
            ->getClient('orbitrondev')
            ->redirect(['user:email', 'user:username', 'user:id']);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     *
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/login-check", name="login_check")
     */
    public function loginCheck(): void
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/user/{username}", name="user")
     */
    public function user()
    {
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
    }
}

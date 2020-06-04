<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     *
     * @param ClientRegistry $clientRegistry The OAuth2 client registry
     *
     * @return RedirectResponse
     */
    public function login(ClientRegistry $clientRegistry)
    {
        /** @var OAuthUser $user */
        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            return $this->redirectToRoute('index');
        }

        return $clientRegistry
            ->getClient('generation2')
            ->redirect(['user:email', 'user:username', 'user:id'])
            ;
    }
}

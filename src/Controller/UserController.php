<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends Controller
{
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
}

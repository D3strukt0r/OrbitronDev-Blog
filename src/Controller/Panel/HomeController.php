<?php

namespace App\Controller\Panel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public static function __setupNavigation()
    {
        return [
            'type' => 'link',
            'parent' => 'root',
            'id' => 'home',
            'title' => 'Dashboard',
            'href' => 'home',
            'icon' => 'hs-admin-panel',
            'view' => 'HomeController::home',
        ];
    }

    public static function __callNumber()
    {
        return 1;
    }

    public function home($navigation, $blog)
    {
        return $this->render('theme_admin1/home.html.twig', [
            'navigation_links' => $navigation,
            'current_blog' => $blog,
        ]);
    }
}

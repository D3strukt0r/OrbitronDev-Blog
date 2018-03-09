<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class SetupController extends Controller
{
    public function setup(Request $request, PdoSessionHandler $sessionHandlerService)
    {
        if ($request->query->get('key') == $this->getParameter('kernel.secret')) {
            if ($request->query->get('action') == 'add-session-table') {
                try {
                    $sessionHandlerService->createTable();
                    return new Response('Session database created');
                } catch (\PDOException $e) {
                    // the table could not be created for some reason
                    return new Response('Session database not created');
                }
            }
            throw $this->createNotFoundException();
        }
        throw $this->createAccessDeniedException();
    }
}

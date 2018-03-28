<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpKernel\KernelInterface;

class SetupController extends Controller
{
    public function setup(Request $request, KernelInterface $kernel, PdoSessionHandler $sessionHandlerService)
    {
        if ($request->query->get('key') == $this->getParameter('kernel.secret')) {
            $output = '';
            try {
                $application = new Application($kernel);
                $application->setAutoExit(false);
                $application->run(new ArrayInput(['command' => 'doctrine:schema:create', '--force']), new NullOutput());
                $output .= '[ <span style="color:green">OK</span> ] Database updated<br />';
            } catch (\Exception $exception) {
                $output .= '[<span style="color:red">FAIL</span>] Database updated ('.$exception->getMessage().')<br />';
            }
            try {
                $sessionHandlerService->createTable();
                $output .= '[ <span style="color:green">OK</span> ] Session table added<br />';
            } catch (\Exception $exception) {
                $output .= '[<span style="color:red">FAIL</span>] Session table added ('.$exception->getMessage().')<br />';
            }
            return new Response('<body style="background-color: black;color: white;"><pre>'.$output.'</pre></body>');
        }
        throw $this->createAccessDeniedException();
    }
}

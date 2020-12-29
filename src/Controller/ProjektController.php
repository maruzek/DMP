<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/projekt", name="projekt")
 */

class ProjektController extends AbstractController
{
    /**
     * @Route("/{name}", name="projekt")
     */
    public function project()
    {
        return $this->render("projects/project.html.twig", [
            'controller_name' => 'ProjektController',
            'name' => "Jane Doe",
            'role' => 'ucitel',
            'username' => 'ruzema'
        ]);
    }

    /**
     * @Route("/novy", name="novy")
     */
    public function novy()
    {
        return $this->render('projects/novy.html.twig', [
            'controller_name' => 'ProjektController',
            'name' => "Jane Doe",
            'role' => 'ucitel'
        ]);
    }
}

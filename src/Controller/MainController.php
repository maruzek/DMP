<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\IndexBlockRepository;
use App\Repository\ProjectRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends AbstractController
{
    private $session;

    /**
     * @Route("/", name="main")
     */
    public function index(SessionInterface $session, IndexBlockRepository $indexBlockRepository)
    {
        $this->session = $session;


        return $this->render('main.html.twig', [
            'controller_name' => 'MainController',
            'session' => $session,
            'indexBlocks' => $indexBlockRepository->findAll()
        ]);
    }

    /**
     * @Route("/seznam", name="list")
     */
    public function list(SessionInterface $session, ProjectRepository $projectRepository)
    {
        $allProjects = $projectRepository->findBy(['deleted' => false]);
        return $this->render('list.html.twig', [
            'session' => $session,
            'allProjects' => $allProjects
        ]);
    }
}

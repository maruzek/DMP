<?php

namespace App\Controller;

use App\ColorTheme\ColorTheme;
use App\Form\SearchType;
use App\PostSeens\PostSeens;
use App\Repository\IndexBlockRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends AbstractController
{
    private $session;
    private $postSeens;

    public function __construct(PostRepository $postRepository, UserRepository $userRepository, SessionInterface $session)
    {
        $this->postSeens = new PostSeens($postRepository, $userRepository, $session);
    }

    /**
     * @Route("/", name="main")
     */
    public function index(SessionInterface $session, IndexBlockRepository $indexBlockRepository, PostRepository $postRepository, UserRepository $userRepository)
    {
        $this->session = $session;

        if ($session->get('username') != null) {
            $user = $userRepository->find($session->get('id'));

            $color = new ColorTheme();
            $palette = $color->colorPallette('white');

            return $this->render('logged.html.twig', [
                'controller_name' => 'MainController',
                'session' => $session,
                'posts' => $this->postSeens->whatUserHasntSeen(),
                'palette' => $palette
            ]);
        }


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

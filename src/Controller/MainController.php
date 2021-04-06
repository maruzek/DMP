<?php

namespace App\Controller;

use App\ColorTheme\ColorTheme;
use App\PostSeens\PostSeens;
use App\Repository\IndexBlockRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

// Hlavní controller

class MainController extends AbstractController
{
    private $postSeens;

    // Konstruktor
    public function __construct(PostRepository $postRepository, UserRepository $userRepository, SessionInterface $session)
    {
        $this->postSeens = new PostSeens($postRepository, $userRepository, $session);
    }

    // Hlavní stránka (index)

    /**
     * @Route("/", name="main")
     */
    public function index(SessionInterface $session, IndexBlockRepository $indexBlockRepository, UserRepository $userRepository)
    {
        // kontrola, zda je uživatel přihlášený
        if ($session->get('username') != null) {
            $user = $userRepository->find($session->get('id'));

            $color = new ColorTheme();
            $palette = $color->colorPallette('white');
            // pokud je přihlášený, vyrenderuje se temaplte 'logged', který vypisuje příspěvky, které uživatel ještě neviděl

            return $this->render('logged.html.twig', [
                'controller_name' => 'MainController',
                'session' => $session,
                'posts' => $this->postSeens->whatUserHasntSeen(),
                'palette' => $palette
            ]);
        }

        // Pokdu přihlášen není, vypíše se hlavní statická stránka (spolu s index bloky)
        return $this->render('main.html.twig', [
            'controller_name' => 'MainController',
            'session' => $session,
            'indexBlocks' => $indexBlockRepository->findAll()
        ]);
    }

    // Route na seznam všech projektů

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

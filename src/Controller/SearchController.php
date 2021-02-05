<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Search\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $session;

    /**
     * @Route("/search/{query?}", name="search")
     */
    public function index($query, SessionInterface $session, Search $search, UserRepository $userRepository, ProjectRepository $projectRepository, PostRepository $postRepository): Response
    {
        $this->session = $session;
        $searchObj = new Search();
        $searchQ = $searchObj->doSearch($query, $userRepository, $postRepository, $projectRepository);
        dump($searchQ);

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'session' => $session,

        ]);
    }
}

<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Search\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $session;

    /**
     * @Route("/search/", name="search")
     */
    public function index(SessionInterface $session, UserRepository $userRepository, ProjectRepository $projectRepository, PostRepository $postRepository, Request $request)
    {
        $this->session = $session;
        $query = $request->query->get('q');
        $searchObj = new Search($userRepository, $postRepository, $projectRepository);
        $searchQ = $searchObj->doSearch($query);
        $searchUsers = $searchQ[0];
        $searchPosts = $searchQ[1];
        $searchProjects = $searchQ[2];
        dump($searchQ);

        //'searchPosts' => $searchQ[1],
        //'searchUsers' => $searchQ[0],
        //'searchProjects' => $searchQ[2],

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'session' => $session,
            'searchPosts' => $searchPosts,
            'searchUsers' => $searchUsers,
            'searchProjects' => $searchProjects
        ]);
    }
}

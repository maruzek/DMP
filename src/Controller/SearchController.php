<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Search\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


// Controller pro hledání 

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(SessionInterface $session, UserRepository $userRepository, ProjectRepository $projectRepository, PostRepository $postRepository, Request $request)
    {
        $query = $request->query->get('q');     // Hledaný výraz
        $searchObj = new Search($userRepository, $postRepository, $projectRepository);      // Přivolání hledacího servisu 
        $searchQ = $searchObj->doSearch($query);
        if ($searchQ != null) {
            $searchUsers = $searchQ[0];
            $searchPosts = $searchQ[1];
            $searchProjects = $searchQ[2];
        } else {
            $searchUsers = "";
            $searchPosts = "";
            $searchProjects = "";
        }

        // render
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'session' => $session,
            'searchPosts' => $searchPosts,
            'searchUsers' => $searchUsers,
            'searchProjects' => $searchProjects
        ]);
    }
}

<?php

namespace App\Search;

use App\Repository\PostRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;

class Search
{
    private string $query;
    private $userRepository;
    private $postRepository;
    private $projectRepository;

    public function __construct(UserRepository $userRepository, PostRepository $postRepository, ProjectRepository $projectRepository)
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->projectRepository = $projectRepository;
    }

    public function doSearch($query)
    {
        $response = [];

        array_push($response, $this->userRepository->searchUser($query));
        array_push($response, $this->postRepository->searchPost($query));
        array_push($response, $this->projectRepository->searchProject($query));

        return $response;
    }
}

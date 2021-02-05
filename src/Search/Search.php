<?php

namespace App\Search;

class Search
{
    private string $query;

    public function doSearch($query, $userRepository, $postRepository, $projectRepository)
    {
        $response = [];

        array_push($response, $userRepository->searchUser($query));
        array_push($response, $postRepository->searchPost($query));
        array_push($response, $projectRepository->searchProject($query));

        return $response;
    }
}

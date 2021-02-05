<?php

namespace App\ProjectCheck;

use App\Repository\ProjectRepository;

class ProjectCheck
{
    private $project;

    public function __construct($id)
    {
        $this->project = $id;
    }

    public function isAccessible(ProjectRepository $projectRepository): bool
    {
        if (!$projectRepository->find($this->project)->getDeleted()) {
            return true;
        } else {
            return false;
        }
    }
}

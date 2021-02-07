<?php

namespace App\ProjectCheck;

use App\Repository\ProjectRepository;

class ProjectCheck
{
    private $project;
    private $projectRepository;

    public function __construct($id, ProjectRepository $projectRepository)
    {
        $this->project = $id;
        $this->projectRepository = $projectRepository;
    }

    public function isAccessible(): bool
    {
        if (!$this->projectRepository->find($this->project)->getDeleted()) {
            return true;
        } else {
            return false;
        }
    }
}

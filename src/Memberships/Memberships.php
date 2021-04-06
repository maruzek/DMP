<?php

namespace App\Memberships;

// Service pro ověření, zda je uživatel členem daného projektu 

class Memberships
{
    public function isUserMember($project, $user)
    {
        $memberships = $user->getMembers();
        $userProjects = [];
        foreach ($memberships as $member) {
            array_push($userProjects, $member->getProject());
        }

        if (in_array($project, $userProjects)) {
            return true;
        } else {
            return false;
        }
    }
}

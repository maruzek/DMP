<?php

namespace App\PostSeens;

use App\Memberships\Memberships;

// Service pro zjišťování, co uživatel již viděl

class PostSeens
{
    private $postRepository;
    private $loggedUser;
    private $userMember;

    public function __construct($postRepository, $userRepository, $session)
    {
        $this->postRepository = $postRepository;
        if ($session->get('id') != null) {
            $this->loggedUser = $userRepository->find($session->get('id'));
        }
        $this->userMember = new Memberships();
    }

    private function hasUserSeenIt($post, $user): bool
    {
        $seens = [];
        foreach ($post->getSeens() as $seen) {
            array_push($seens, $seen->getuser());
        }

        if (in_array($user, $seens)) {
            return true;
        } else {
            return false;
        }
    }

    public function whatUserHasntSeen(): array
    {
        $allPosts = [];

        foreach ($this->loggedUser->getFollows() as $follow) {
            foreach ($this->postRepository->findBy(['project' => $follow->getProject(), 'deleted' => false]) as $post) {
                if (!in_array($post, $allPosts) && $post->getPrivacy() == 0) {
                    array_push($allPosts, $post);
                }
            }
        }
        foreach ($this->loggedUser->getMembers() as $member) {
            foreach ($this->postRepository->findBy(['project' => $member->getProject(), 'deleted' => false]) as $post) {
                if (!in_array($post, $allPosts)) {
                    if ($post->getPrivacy() == 1) {
                        if ($this->userMember->isUserMember($post->getProject(), $this->loggedUser)) {
                            array_push($allPosts, $post);
                        }
                    } else {
                        array_push($allPosts, $post);
                    }
                }
            }
        }

        $seenPosts = [];
        foreach ($allPosts as $post) {
            if (!$this->hasUserSeenIt($post, $this->loggedUser)) {
                array_push($seenPosts, $post);
            }
        }

        return $seenPosts;
    }
}

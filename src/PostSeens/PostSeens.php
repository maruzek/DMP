<?php

namespace App\PostSeens;

class PostSeens
{
    private $postRepository;
    private $userRepository;
    private $session;
    private $loggedUser;

    public function __construct($postRepository, $userRepository, $session)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->session = $session;
        if ($session->get('id') != null) {
            $this->loggedUser = $userRepository->find($session->get('id'));
        }
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
            foreach ($this->postRepository->findBy(['project' => $follow->getProject()]) as $post) {
                if (!in_array($post, $allPosts)) {
                    array_push($allPosts, $post);
                }
            }
        }
        foreach ($this->loggedUser->getMembers() as $member) {
            foreach ($this->postRepository->findBy(['project' => $member->getProject()]) as $post) {
                if (!in_array($post, $allPosts)) {
                    array_push($allPosts, $post);
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

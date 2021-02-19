<?php

namespace App\Authentication;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Authentication
{
    private $session;
    private $userRepository;

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    public function isAbsAdmin(): bool
    {
        if (!$this->session->get('id') == null) {
            $user = $this->userRepository->find($this->session->get('id'));
            if ($user->getRole() == "admin") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isLoggedUser($id): bool
    {
        if ($this->session->get('id') == $id) {
            return true;
        } else {
            return false;
        }
    }
}

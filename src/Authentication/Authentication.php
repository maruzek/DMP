<?php

namespace App\Authentication;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Authentication
{
    private int $userId;
    private $session;

    public function __constructor($id)
    {
        $this->userId = $id;
    }

    public function isAbsAdmin(SessionInterface $session, UserRepository $userRepository): bool
    {
        $this->session = $session;
        if (!$session->get('id') == null) {
            $user = $userRepository->find($session->get('id'));
            if (in_array("ROLE_ADMIN", $user->getRoles())) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isLoggedUser()
    {
    }
}

<?php

namespace App\Authentication;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

// Service, který slouží k ověřování uživatelů
class Authentication
{
    private $session;
    private $userRepository;

    // konstruktor

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    // Funkce, která prověřuje, zda je uživatel adminem aplikace

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

    // Funkce, která zjišťuje, zda je uživatel snažící se někam dostat, je ten, který je zároveň přihlášený

    public function isLoggedUser($id): bool
    {
        if ($this->session->get('id') == $id) {
            return true;
        } else {
            return false;
        }
    }
}

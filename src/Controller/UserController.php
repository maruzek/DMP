<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/{username}/nastaveni", name="settings")
     */
    public function settings($username)
    {
        return $this->render("user/settings.html.twig", [
            'controller_name' => 'UserController',
            'name' => "Jane Doe",
            'role' => 'ucitel'
        ]);
    }

    /**
     * @Route("/{username}", name="profile")
     */
    public function profile($username)
    {
        //! NEFUNGUJE, DODĚLAT
        if ($username == "nastaveni") {
            $this->redirect("/user/ruzema/nastaveni");
        } else {
            return $this->render("user/profile.html.twig", [
                'controller_name' => 'UserController',
                'name' => "Jane Doe",
                'role' => 'ucitel',
                'username' => $username
            ]);
        }
    }

    /**
     * @Route("/{username}/odebirane", name="subscribed")
     */
    public function subscriber($username)
    {
        return $this->render("subscribed.html.twig", [
            'controller_name' => 'UserController',
            'name' => "Jane Doe",
            'role' => 'ucitel',
            'username' => $username
        ]);
    }
}

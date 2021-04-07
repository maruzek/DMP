<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\SSO\SSO;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

// Controller pro přihlašování
class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function index(Request $request, SessionInterface $session, UserRepository $userRepository): Response
    {
        // Základní URL pro SSO
        $ssoUrlBase = "https://titan.spsostrov.cz/ssogw/";

        $callbackUrl = $this->generateUrl('login', [], Router::ABSOLUTE_URL);


        $service = base64_encode($callbackUrl);     // Vytvoření tokenu pro přihlášení

        $ssoUrl = "";

        // Pokud je ticket již vytvořen (to se stane, když je sem uživatel přesměrován z titanu)
        if ($request->query->get('ticket')) {
            $token = $request->query->get('ticket');

            $ssoUserDataRequestingUrl = $ssoUrlBase .
                "service-check.php?service=" .
                urlencode($service) .
                "&ticket=" .
                urlencode($token);

            // Získání dat
            $data = file_get_contents($ssoUserDataRequestingUrl);

            // Zavolání služby, která se stará o Zpracování dat uživatele 

            $sso = new SSO($data);
            $ssoData = $sso->getAllData();

            $userDB = $userRepository->findOneBy(['username' => $ssoData['login']]);        // kontrola DB pokud již uživatel v DB aplikace existuje

            if (!$userDB) {
                // Pokud ne, vytvoří se jeho záznam
                $user = new User();

                $user
                    ->setUsername($ssoData['login'])
                    ->setClass($ssoData['class'])
                    ->setRole($ssoData['role'])
                    ->setFirstname($ssoData['firstname'])
                    ->setLastname($ssoData['lastname'])
                    ->setTag($ssoData['tag'])
                    ->setFirstLogin(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } else {
                $user = $userDB;
                if ($user->getDeactivated()) {
                    return $this->redirectToRoute('main', ['deactivated' => true]);
                }
            }
            // Zapsání do session

            $session->set('username', $ssoData['login']);
            $session->set('class', $ssoData['class']);
            $session->set('firstName', $ssoData['firstname']);
            $session->set('lastName', $ssoData['lastname']);
            $session->set('role', $user->getRole());
            $session->set('tag', $ssoData['tag']);
            $session->set('id', $user->getId());
            $session->set('user', $user);

            return $this->redirectToRoute('main');   // Přesměrování na hlavní stránku aplikace
        } else {
            // Pokud uživatel nemá vytvořený token, SSO mu ho na této adrese vytvoří
            $ssoUrl = $ssoUrlBase . "?service=" . urlencode($service);

            return $this->redirect($ssoUrl);
        }

        return $this->redirectToRoute('main');
    }
}

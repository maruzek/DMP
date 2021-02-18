<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    private $session;

    /**
     * @Route("/login", name="login")
     */
    public function index(SessionInterface $session): Response
    {
        // set the base url for the SSO application
        $ssoUrlBase = "https://titan.spsostrov.cz/ssogw/";

        #compose the callback URL (may not work in all circumstances)
        //$protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
        $protocol = "http";
        $callbackUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $callbackUrl = $protocol . "://localhost:8081/login";

        #create the service token
        $service = base64_encode($callbackUrl);

        $ssoUrl = "";

        if (isset($_GET['ticket'])) {
            //if the ticket parameter is set, it means that SSO Application already
            //redirected back

            $token = $_GET['ticket'];

            $ssoUserDataRequestingUrl = $ssoUrlBase .
                "service-check.php?service=" .
                urlencode($service) .
                "&ticket=" .
                urlencode($token);

            //get the user data
            $data = file_get_contents($ssoUserDataRequestingUrl);

            $strposLogin = strpos($data, "login");
            $strposName = strpos($data, "name");
            $strposGroup = strpos($data, "group");
            $strposGroupName = strpos($data, "group_name");
            $strposAuth = strpos($data, 'auth_by');
            $strposOu = strpos($data, "ou_simple");
            $dataLength = strlen($data);

            $substr2 = substr($data, $strposLogin, $strposName - $strposLogin);
            $exp = explode(':', $substr2);

            $substrName = substr($data, $strposName, $strposGroup - $strposName);
            $expName = explode(':', $substrName);

            $substrGroup = substr($data, $strposGroup, $strposGroupName - $strposGroup);
            $expGroup = explode(':', $substrGroup);

            $substrAuth = substr($data, $strposAuth, $strposOu - $strposAuth);
            $expAuth = explode(':', $substrAuth);

            $substrOu = substr($data, $strposOu, $dataLength - $strposOu);
            $expOu = explode(':', $substrOu);

            $ou = str_split($expOu[1]);
            switch ($ou[0]) {
                case "s":
                    $userGroup = "ROLE_STUDENT";
                    $tag = "student";
            }
            $cou = 0;

            if (trim($expName[1][0]) == "x") {
                $userGroup = "ROLE_STUDENT";
                $tag = "student";

                $name = trim($expName[1]);
                $name = explode('.', $name);

                $username = trim($exp[1]);
                $firstName = $name[0];
                $lastName = $name[1];
                $role = $userGroup;
                $class = "AM3";
            } else {
                if (count($ou) == 5) {
                    $cou = 4;
                    $classYear = (int)$ou[1] . $ou[2];
                    if (date('n') <= 12 && date('n') >= 9) {
                        $classNum = (int)date('y') - ($classYear - 1);
                    } else {
                        $classNum = (int)date('y') - ($classYear);
                    }
                    $class = trim(strtoupper($ou[3]) . $classNum);
                } elseif (count($ou) == 6) {
                    $cou = 5;
                    $classYear = (int)$ou[1] . $ou[2];
                    $classNum = (int)date('y') - ($classYear - 1);
                    $class = trim(strtoupper($ou[3]) . strtoupper($ou[4]) . $classNum);
                }

                $name = trim($expName[1]);
                $name = explode(' ', $name);

                $username = trim($exp[1]);
                $firstName = $name[0];
                $lastName = $name[1];
                $role = $userGroup;
                $role = "ROLE_ADMIN";
            }


            $user = new User();

            $user
                ->setUsername($username)
                ->setClass($class)
                ->setRoles([$role])
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setTag($tag);

            $em = $this->getDoctrine()->getManager();

            $em->persist($user);

            $users = $this->getDoctrine()->getRepository(User::class)->findBy([
                'username' => $username
            ]);

            if (count($users) > 0) {
                $error = "Už tam byl";
            } else {
                $em->flush();
                $error = "Vytvořil jsem ho";
            }

            $users = $this->getDoctrine()->getRepository(User::class)->findBy([
                'username' => $username
            ]);

            $this->session = $session;

            $roles = $users[0]->getRoles();

            $this->session->set('username', $username);
            $this->session->set('class', $class);
            $this->session->set('firstName', $firstName);
            $this->session->set('lastName', $lastName);
            $this->session->set('role', $roles);
            $this->session->set('tag', $tag);
            $this->session->set('id', $users[0]->getId());
            $this->session->set('user', $user);

            return $this->redirect('/');
        } else {
            $ssoUrl = $ssoUrlBase . "?service=" . urlencode($service);

            return $this->redirect($ssoUrl);
        }

        if ($this->session->get('username') != "") {
            $userProfile = $this->generateUrl('user.profile', [
                'username' => $this->session->get('username')
            ]);
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'session' => $session,
            'userprofile' => $userProfile
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(SessionInterface $session, Request $request)
    {
        $session->remove('user');
        $session->remove('username');
        $session->remove('id');
        $session->remove('class');
        $session->remove('role');
        $session->remove('tag');
        $session->remove('lastName');
        $session->remove('firstName');

        /*$response = new Response();
        $response->headers->clearCookie('PHPSESSID', '/', null);
        unset($_COOKIE['PHPSESSID']);

        return $this->render('login/logout.html.twig', [
            'session' => $session
        ], $response);*/

        return $this->redirect('/');
    }
}

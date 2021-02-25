<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
    public function index(SessionInterface $session, UserRepository $userRepository): Response
    {
        // set the base url for the SSO application
        $ssoUrlBase = "https://titan.spsostrov.cz/ssogw/";
        // Normal version
        $callbackUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL'];
        // Wedos version
        //$callbackUrl = "https://dmp.martinruzek.eu/login";

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

            $responseFile = file_get_contents(__DIR__ . '/../response.json');
            $json = json_decode($responseFile, true);

            $newResponse = [
                'date' => date('Y-m-d H:i:s'),
                'response' => $data
            ];

            array_push($json, $newResponse);
            $json = json_encode($json);
            file_put_contents(__DIR__ . '/../response.json', $json);

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
                    $role = "user";
                    $tag = "student";
                    break;
                case 'u':
                    $role = "user";
                    $tag = "učitel";
                    break;
            }
            $cou = 0;

            if (trim($expName[1][0]) == "x") {
                $userGroup = "sudent";
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
                if ($username == "ceska") {
                    $role = "admin";
                }
                $firstName = $name[0];
                $lastName = $name[1];
                //$role = "admin";
            }

            $userDB = $userRepository->findOneBy(['username' => $username]);

            if (!$userDB) {
                $user = new User();

                $user
                    ->setUsername($username)
                    ->setClass($class)
                    ->setRole($role)
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setTag($tag);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } else {
                $user = $userDB;
            }

            $this->session = $session;

            $this->session->set('username', $username);
            $this->session->set('class', $class);
            $this->session->set('firstName', $firstName);
            $this->session->set('lastName', $lastName);
            $this->session->set('role', $user->getRole());
            $this->session->set('tag', $tag);
            $this->session->set('id', $user->getId());
            $this->session->set('user', $user);

            return $this->redirect('/');
        } else {
            $ssoUrl = $ssoUrlBase . "?service=" . urlencode($service);

            return $this->redirect($ssoUrl);
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'session' => $session
        ]);
    }
}

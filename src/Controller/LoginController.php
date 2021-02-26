<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\SSO\SSOResponse\SSOResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
    public function index(Request $request, SessionInterface $session, UserRepository $userRepository): Response
    {
        // set the base url for the SSO application
        $ssoUrlBase = "https://titan.spsostrov.cz/ssogw/";
        // Normal version
        //$callbackUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL'];
        $callbackUrl = $this->generateUrl('login', [], Router::ABSOLUTE_URL);

        // Wedos version
        //$callbackUrl = "https://dmp.martinruzek.eu/login";

        #create the service token
        $service = base64_encode($callbackUrl);

        $ssoUrl = "";

        if ($request->query->get('ticket')) {
            //if the ticket parameter is set, it means that SSO Application already
            //redirected back

            $token = $request->query->get('ticket');

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

            $sso = new SSOResponse($data);
            $ssoData = $sso->getAllData();

            $userDB = $userRepository->findOneBy(['username' => $ssoData['login']]);

            if (!$userDB) {
                $user = new User();

                $user
                    ->setUsername($ssoData['login'])
                    ->setClass($ssoData['class'])
                    ->setRole($ssoData['role'])
                    ->setFirstname($ssoData['firstname'])
                    ->setLastname($ssoData['lastname'])
                    ->setTag($ssoData['tag']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } else {
                $user = $userDB;
            }

            $this->session = $session;

            $this->session->set('username', $ssoData['login']);
            $this->session->set('class', $ssoData['class']);
            $this->session->set('firstName', $ssoData['firstname']);
            $this->session->set('lastName', $ssoData['lastname']);
            $this->session->set('role', $user->getRole());
            $this->session->set('tag', $ssoData['tag']);
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

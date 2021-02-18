<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\IndexBlockRepository;
use App\Repository\ProjectRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MainController extends AbstractController
{
    private $session;

    /**
     * @Route("/", name="main")
     */
    public function index(SessionInterface $session, IndexBlockRepository $indexBlockRepository)
    {
        $this->session = $session;


        return $this->render('main.html.twig', [
            'controller_name' => 'MainController',
            'session' => $session,
            'indexBlocks' => $indexBlockRepository->findAll()
        ]);
    }

    /**
     * @Route("/seznam", name="list")
     */
    public function list(SessionInterface $session, ProjectRepository $projectRepository)
    {
        $allProjects = $projectRepository->findBy(['deleted' => false]);
        return $this->render('list.html.twig', [
            'session' => $session,
            'allProjects' => $allProjects
        ]);
    }

    /**
     * @Route("/ssologin", name="sso")
     */
    public function sso(Request $request)
    {
        // set the base url for the SSO application
        $ssoUrlBase = "https://titan.spsostrov.cz/ssogw/";

        #compose the callback URL (may not work in all circumstances)
        //$protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
        $protocol = "http";
        $callbackUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
        $callbackUrl = $protocol . "://localhost:8081/ssologin";

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
            $classYear = (int)$ou[1] . $ou[2];
            $classNum = (int)date('y') - ($classYear - 1);
            $class = strtoupper($ou[3]) . $classNum;

            switch ($ou[0]) {
                case "s":
                    $userGroup = "student";
            }


            //output user data
            //header('Content-Type: text/plain');
            //echo $data;
        } else {
            //if the ticket parameter is not set, it means we are starting and we should
            //redirect to the SSO Application
            $ssoUrl = $ssoUrlBase . "?service=" . urlencode($service);

            //redirect to the SSO Application

            return $this->redirect($ssoUrl);
            //header("Location: " . $ssoUrl);
        }

        return $this->render('sso.html.twig', [
            'controller_name' => 'MainController',
            'server_name' => $_SERVER['SERVER_NAME'],
            'script_name' => $_SERVER['SCRIPT_NAME'],
            'callback' => $callbackUrl,
            'base' => $service,
            'data' => $data,
            'ssoUrl' => $ssoUrl,
            'strp' => $strposLogin,
            'substr2' => $substr2,
            'username' => $exp[1],
            'expName' => $expName[1],
            'expGroup' => $expGroup[1],
            'expAuth' => $expAuth[1],
            'expOu' => $expOu[1],
            'name' => "Jane Doe",
            'role' => 'ucitel',
            'classYear' => $classYear,
            'classNum' => $classNum,
            'class' => $class,
            'userGroup' => $userGroup
        ]);
    }
}

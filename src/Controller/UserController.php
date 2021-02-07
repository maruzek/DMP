<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\Entity\Project;
use App\Form\UserSettingsType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/user", name="user.")
 */
class UserController extends AbstractController
{
    private $session;
    private $userRepository;
    private $auth;

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->auth = new Authentication($session, $userRepository);
    }

    /**
     * @Route("/userSettings", name="userSettings", methods={"POST"})
     */
    public function userSettings(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $data = $request->request->get('image');

            $em = $this->getDoctrine()->getManager()->flush();


            $defaultContext = [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getId();
                },
            ];
            $encoders = [
                new JsonEncoder()
            ];
            $normalizers = [
                new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)
            ];
            $serializer = new Serializer($normalizers, $encoders);
            $response = $serializer->serialize($data, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    /**
     * @Route("/{username}", name="profile")
     */
    public function index($username, SessionInterface $session, UserRepository $userRepository, Request $request): Response
    {
        $this->session = $session;

        $user = $userRepository->findOneBy(['username' => $username]);

        $form = $this->createForm(UserSettingsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $request->files->get('user_settings')["attach"];
            if ($file) {
                $ext = $file->guessClientExtension();
                if ($ext == "jpeg" || $ext == "jpg" || $ext == "jfif" || $ext == "png") {
                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                    $file->move(
                        $this->getParameter('user_pic'),
                        $filename
                    );
                    if (touch($user->getImage()) && $user->getImage() != 'default.png') {
                        unlink($this->getParameter('user_pic') . '/' . $user->getImage());
                    }

                    $user->setImage($filename);
                } else {
                    $fileError = "badext";
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $status = "success";
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'session' => $session,
            'user' => $user,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/{username}/odebirane", name="odebirane")
     */
    public function odebirane(SessionInterface $session): Response
    {
        $this->session = $session;

        $userProfile = $this->generateUrl('user.profile', [
            'username' => $this->session->get('username')
        ]);

        return $this->render('user/odebirane.html.twig', [
            'controller_name' => 'UserController',
            'session' => $session,
            'userprofile' => $userProfile
        ]);
    }

    /**
     * @Route("/{username}/mojeprojekty", name="projects")
     */
    public function projects($username, SessionInterface $session, UserRepository $userRepository, ProjectRepository $projectRepository): Response
    {
        $this->session = $session;
        if ($username != $session->get('username')) {
        } else {
            $user = $userRepository->findBy(['username' => $username]);
            $projects = $projectRepository->findAll(['admin' => $user]);
        }
        return $this->render('user/projects.html.twig', [
            'controller_name' => 'UserController',
            'session' => $session,
            'username' => $username,
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/{username}/nastaveni", name="settings")
     */
    public function settings($username, SessionInterface $session, UserRepository $userRepository, ProjectRepository $projectRepository, Request $request): Response
    {
        $this->session = $session;

        $user = $userRepository->find($session->get('id'));

        $form = $this->createForm(UserSettingsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $request->files->get('user_settings')["attach"];
            if ($file) {
                $ext = $file->guessClientExtension();
                if ($ext == "jpeg" || $ext == "jpg" || $ext == "jfif" || $ext == "png") {
                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                    $file->move(
                        $this->getParameter('user_pic'),
                        $filename
                    );
                    if (touch($user->getImage())) {
                        unlink($this->getParameter('user_pic') . '/' . $user->getImage());
                    }

                    $user->setImage($filename);
                } else {
                    $fileError = "badext";
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $status = "success";
        }

        return $this->render('user/settings.html.twig', [
            'controller_name' => 'UserController',
            'session' => $session,
            'form' => $form->createView()
        ]);
    }
}

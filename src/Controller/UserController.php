<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\Entity\Project;
use App\Form\UserSettingsType;
use App\ImageCrop\ImageCrop;
use App\Repository\FollowRepository;
use App\Repository\MemberRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @Route("/{username}", name="profile")
     */
    public function index($username, SessionInterface $session, UserRepository $userRepository, Request $request, ValidatorInterface $validator): Response
    {
        $this->session = $session;

        if ($user = $userRepository->findOneBy(['username' => $username])) {
            $form = $this->createForm(UserSettingsType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if (count($validator->validate($user)) <= 0) {
                    $file = $request->files->get('user_settings')["attach"];
                    if ($file) {
                        $img = new ImageCrop($file, $this->getParameter('user_pic'), $this->getDoctrine()->getManager());
                        if ($img->cropUserImage($user)) {
                            $filestatus = "success";
                        } else {
                            $filestatus = "fail";
                        }
                    }

                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                } else {
                    //! ERROR
                }
            }

            $publicPosts = [];
            foreach ($user->getPosts() as $post) {
                if ($post->getPrivacy() == false && $post->getDeleted() == false) {
                    array_push($publicPosts, $post);
                }
            }

            return $this->render('user/index.html.twig', [
                'controller_name' => 'UserController',
                'session' => $session,
                'user' => $user,
                'form' => $form->createView(),
                'publicPosts' => $publicPosts
            ]);
        }

        return new Response('', 404);
    }

    /**
     * @Route("/{username}/mojeprojekty", name="projects")
     */
    public function projects($username, SessionInterface $session, UserRepository $userRepository, ProjectRepository $projectRepository, MemberRepository $memberRepository, FollowRepository $followRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        if ($this->auth->isLoggedUser($user->getId())) {
            if ($username != $session->get('username')) {
            } else {
                $members = $memberRepository->findBy(['member' => $user]);
                $memberProjects = [];
                foreach ($members as $member) {
                    array_push($memberProjects, $member->getProject());
                }

                $follows = $followRepository->findBy(['follower' => $user]);
                $followProjects = [];
                foreach ($follows as $follow) {
                    array_push($followProjects, $follow->getProject());
                }
            }
            return $this->render('user/projects.html.twig', [
                'controller_name' => 'UserController',
                'session' => $session,
                'username' => $username,
                'memberProjects' => $memberProjects,
                'followProjects' => $followProjects
            ]);
        }

        return new Response('', 401);
    }
}

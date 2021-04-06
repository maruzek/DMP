<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\Form\UserSettingsType;
use App\ImageCrop\ImageCrop;
use App\Repository\FollowRepository;
use App\Repository\MemberRepository;
use App\Repository\UserRepository;
use App\ValidateImage\ValidateImage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// Uživatelský controller

/**
 * @Route("/user", name="user.")
 */
class UserController extends AbstractController
{
    private $auth;

    // konstruktor

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        $this->auth = new Authentication($session, $userRepository);
    }

    // Endpoint pro uživatelský profil

    /**
     * @Route("/{username}", name="profile")
     */
    public function index($username, SessionInterface $session, UserRepository $userRepository, Request $request, ValidatorInterface $validator): Response
    {
        // kontrola, pokud hledaný uživatel existuje v DB 
        if ($user = $userRepository->findOneBy(['username' => $username])) {
            $errors = [];
            // Form pro nastavení profilu
            $err = "";
            $form = $this->createForm(UserSettingsType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $imgValidator = new ValidateImage();
                if (count($validator->validate($user)) <= 0) {
                    $file = $request->files->get('user_settings')["attach"];
                    if ($file) {
                        // Zpracování obrázku

                        $validation = $imgValidator->isImgValid($file);
                        if ($validation == "success") {
                            $img = new ImageCrop($file, $this->getParameter('user_pic'), $this->getDoctrine()->getManager());
                            $img->cropUserImage($user);
                        } elseif ($validation == "badsize") {
                            array_push($errors, 'Vámi nahrávaný obrázek má moc veliké rozměry');
                            $err = "chyba";
                        } elseif ($validation == "toobig") {
                            array_push($errors, 'Vámi nahrávaný obrázek je moc velký (maximální velikost 2 MB)');
                            $err = "chyba";
                        } elseif ($validation == "badext") {
                            array_push($errors, 'Vámi nahrávaný obrázek je nepodporovaného typu (povolené jsou jen .png, .jpg a .jpeg). Také se mohlo stát, že je váš obrázek poškozený.');
                            $err = "chyba";
                        }
                    }
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    return $this->redirect($request->getUri());
                } else {
                    array_push($errors, 'Někde se stala chyba. Provděpodobně máte bio profilu delší než 140 znaků');
                }
            }
            $publicPosts = [];
            foreach ($user->getPosts() as $post) {
                if ($post->getPrivacy() == false && $post->getDeleted() == false && $post->getPrivacy() == false) {
                    array_push($publicPosts, $post);
                }
            }

            return $this->render('user/index.html.twig', [
                'controller_name' => 'UserController',
                'session' => $session,
                'user' => $user,
                'form' => $form->createView(),
                'publicPosts' => $publicPosts,
                'errors' => $errors
            ]);
        }

        return new Response('', 404);
    }

    // Endpoin pro výpis projektů, kterých je uživatel členem (ostatní si tuto stránku zobrazit nemohou)

    /**
     * @Route("/{username}/mojeprojekty", name="projects")
     */
    public function projects($username, SessionInterface $session, UserRepository $userRepository, MemberRepository $memberRepository, FollowRepository $followRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        if ($this->auth->isLoggedUser($user->getId())) {

            if ($username == $session->get('username')) {
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
                'memberProjects' => $memberProjects,
                'followProjects' => $followProjects
            ]);
        }

        return new Response('', 401);
    }
}

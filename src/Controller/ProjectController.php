<?php

namespace App\Controller;

use App\ColorTheme\ColorTheme;
use App\Entity\Follow;
use App\Entity\Media;
use App\Entity\Member;
use App\Entity\Post;
use App\Entity\Project;
use App\Form\AddPostType;
use App\Form\FollowType;
use App\Form\MemberType;
use App\Form\NewProjectType;
use App\Form\ProjectSettingsType;
use App\Form\UnfollowType;
use App\Form\UnmemberType;
use App\Repository\FollowRepository;
use App\Repository\MemberRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/projekt", name="project.")
 */
class ProjectController extends AbstractController
{
    private $session;

    /**
     * @Route("/novy", name="new")
     */
    public function new(SessionInterface $session, Request $request, UserRepository $userRepository): Response
    {
        // Volání session    
        $this->session = $session;

        // Vytvoření objektu nového projektu
        $project = new Project();

        $user = $userRepository->find($session->get('id'));
        $project->setAdmin($user);
        $form = $this->createForm(NewProjectType::class, $project);

        // Získání odpovědi z formu
        $form->handleRequest($request);
        $formResponse = "";
        $id = "";
        $fileError = "";
        if ($form->isSubmitted()) {
            // Zpracování nahraného obrázku
            /** @var UploadedFile $file*/
            $file = $request->files->get('new_project')['attach'];
            if ($file) {
                $ext = $file->guessClientExtension();
                if ($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif") {
                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                    $file->move(
                        $this->getParameter('project_pic'),
                        $filename
                    );
                    $project->setImage($filename);
                } else {
                    $fileError = "badext";
                }
            }

            if (!$fileError) {
                // Zapsání do DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($project);
                $em->flush();
                $formResponse = "success";
                $id = $project->getId();

                // Přesměronání na nově vytvořený projekt
                return $this->redirectToRoute('project.project', ['id' => $project->getId()]);
            } else {
            }
        }

        return $this->render('project/new.html.twig', [
            'controller_name' => 'ProjectController',
            'session' => $session,
            'form' => $form->createView(),
            'response' => $formResponse,
            'id' => $id,
            'fileError' => $fileError
        ]);
    }

    /**
     * @Route("/{id}", name="project")
     */
    public function index($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, FollowRepository $followRepository, Request $request, MemberRepository $memberRepository): Response
    {
        $this->session = $session;

        if ($project = $projectRepository->findOneBy(['id' => $id])) {
            $follow = new Follow();
            $member = new Member();
            $color = new ColorTheme();
            $palette = $color->colorPallette($project->getColor());


            if ($session->get('username') != "") {
                $user = $userRepository->find($session->get('id'));

                $follow->setFollower($user);
                $follow->setProject($project);

                $member->setProject($project);
                $member->setMember($user);
                $member->setAccepted("false");

                if (!$followRepository->findOneBy(['follower' => $session->get('id'), 'project' => $project])) {
                    $followForm = $this->createForm(FollowType::class, $follow);
                } else {
                    $followForm = $this->createForm(UnfollowType::class, $follow);
                }

                if (!$memberRepository->findOneBy(['member' => $user, 'project' => $project])) {
                    $memberForm = $this->createForm(MemberType::class, $member);
                } else {
                    $memberForm = $this->createForm(UnmemberType::class, $member);
                }

                $followStatus = '';
                $followForm->handleRequest($request);
                if ($followForm->isSubmitted()) {
                    $em = $this->getDoctrine()->getManager();
                    $follower = $userRepository->find($session->get('id'));

                    if ($followRepository->findOneBy(['follower' => $session->get('id'), 'project' => $project])) {
                        $unfollow = $em->getRepository(Follow::class)->findOneBy([
                            'project' => $project,
                            'follower' => $follower
                        ]);
                        $em->remove($unfollow);
                        $em->flush();
                        $followStatus = 'unfollowed';
                    } else {
                        $em->persist($follow);
                        $em->flush();
                        $followStatus = 'success';
                    }
                }

                $memberStatus = "";
                $memberForm->handleRequest($request);
                if ($memberForm->isSubmitted()) {
                    $em = $this->getDoctrine()->getManager();
                    if ($memberRepository->findOneBy(['member' => $user, 'project' => $project])) {
                        $unmember = $em->getRepository(Member::class)->findOneBy([
                            'project' => $project,
                            'member' => $user
                        ]);
                        $em->remove($unmember);
                        $em->flush();
                        $memberStatus = "unmember";
                    } else {
                        $em->persist($member);
                        $em->flush();
                        $memberStatus = "newmember";
                    }
                }

                if (!$followRepository->findOneBy(['follower' => $session->get('id'), 'project' => $project])) {
                    $followForm = $this->createForm(FollowType::class, $follow);
                } else {
                    $followForm = $this->createForm(UnfollowType::class, $follow);
                }

                if (!$memberRepository->findOneBy(['member' => $user, 'project' => $project])) {
                    $memberForm = $this->createForm(MemberType::class, $member);
                } else {
                    $memberForm = $this->createForm(UnmemberType::class, $member);
                }

                $settingsForm = $this->createForm(ProjectSettingsType::class, $project);
                $fileError = "";
                $mediaError = "";
                $settingsForm->handleRequest($request);
                if ($settingsForm->isSubmitted()) {
                    // Zpracování profilového obrázku
                    $file = $request->files->get('project_settings')["attach"];
                    if ($file) {
                        $ext = $file->guessClientExtension();
                        if ($ext == "jpeg" || $ext == "jpg" || $ext == "jfif" || $ext == "png") {
                            $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                            $file->move(
                                $this->getParameter('project_pic'),
                                $filename
                            );
                            unlink($this->getParameter('project_pic') . '/' . $project->getImage());
                            $project->setImage($filename);
                        } else {
                            $fileError = "badext";
                        }
                    }

                    // Zavolání entitty managera
                    $em = $this->getDoctrine()->getManager();
                    // Zpracování obrázků v pozadí
                    $media = $request->files->get('project_settings')["medias"];
                    if ($media) {
                        /** @var UploadedFile $file*/
                        foreach ($media as $file) {
                            $ext = $file->guessClientExtension();
                            if ($ext == "jpeg" || $ext == "jpg" || $ext == "jfif" || $ext == "png") {
                                $height = getimagesize($file)[1];
                                if ($height) {
                                    //TODO Přidat velikost 480 zpět do ifu
                                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                                    $file->move(
                                        $this->getParameter('media'),
                                        $filename
                                    );
                                    $newMedia = new Media();
                                    $newMedia->setName($filename);
                                    $newMedia->setProject($project);
                                    $newMedia->setType('hero');
                                    $newMedia->setUploader($user);
                                    $em->persist($newMedia);
                                } else {
                                    $mediaError = "badheight";
                                }
                            } else {
                                $mediaError = "badext";
                            }
                        }
                    }

                    // Mazání obrázků v pozadí

                    $heroImages = $project->getMedia();
                    $heroImagesArray = [];

                    for ($i = 0; $i < count($heroImages); $i++) {
                        if ($heroImages[$i]->getType('hero')) {
                            array_push($heroImagesArray, $heroImages[$i]);
                        }
                    }
                    for ($i = 0; $i < count($heroImagesArray); $i++) {
                        if (isset($request->request->get('project_settings')['media'][$i])) {
                            if (file_exists('img/media/' . $heroImagesArray[$i]->getName())) {
                                unlink('img/media/' . $heroImagesArray[$i]->getName());
                                echo 'penis';
                            }
                            $em->remove($heroImagesArray[$i]);
                        }
                    }

                    // Pokud se vše podaří, zapíše do DB a refreshne stránku
                    if ($fileError == "" && $mediaError == "") {
                        $em->flush();
                        $status = "success";
                        return $this->redirect($request->getUri());
                    }
                }

                // Zpracování nového příspěvku
                $post = new Post();
                $post->setProject($project);
                $post->setAuthor($user);
                $addPostForm = $this->createForm(AddPostType::class, $post);

                $addPostForm->handleRequest($request);
                if ($addPostForm->isSubmitted()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($post);
                    $em->flush();
                    unset($post);
                    unset($addPostForm);
                    $post = new Post();
                    $addPostForm = $this->createForm(AddPostType::class, $post);
                }
            } else {
            }

            if (isset($followForm)) {
                return $this->render('project/index.html.twig', [
                    'controller_name' => 'ProjectController',
                    'session' => $session,
                    'project' => $project,
                    'followForm' => $followForm->createView(),
                    'followStatus' => $followStatus,
                    'palette' => $palette,
                    'pic' => $this->getParameter('project_pic'),
                    'memberForm' => $memberForm->createView(),
                    'memberStatus' => $memberStatus,
                    'postForm' => $addPostForm->createView(),
                    'projectSettingsForm' => $settingsForm->createView(),
                    'mediaError' => $mediaError
                ]);
            } else {
                return $this->render('project/index.html.twig', [
                    'controller_name' => 'ProjectController',
                    'session' => $session,
                    'project' => $project,
                    'palette' => $palette,
                ]);
            }
        } else {
            return $this->render('project/index.html.twig', [
                'controller_name' => 'ProjectController',
                'session' => $session
            ]);
        }
    }

    /**
     * @Route("/{id}/nastaveni", name="settings")
     */
    public function settings($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, FollowRepository $followRepository, Request $request): Response
    {
        $this->session = $session;
        $status = "";
        $project = "";
        $fileError = "";
        if ($session->get('username') != "") {
            $project = $projectRepository->findOneBy(['id' => $id]);
            $user = $userRepository->find($session->get('id'));

            if ($project != "" && $project->getAdmin() == $user) {
                $auth = "yes";
                $project = $projectRepository->find($id);

                $form = $this->createForm(ProjectSettingsType::class, $project);

                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    $file = $request->files->get('project_settings')["attach"];
                    if ($file) {
                        $ext = $file->guessClientExtension();
                        if ($ext == "jpeg" || $ext == "jpg" || $ext == "jfif" || $ext == "png") {
                            $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                            $file->move(
                                $this->getParameter('project_pic'),
                                $filename
                            );
                            unlink($this->getParameter('project_pic') . '/' . $project->getImage());
                            $project->setImage($filename);
                        } else {
                            $fileError = "badext";
                        }
                    }
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    $status = "success";
                }
            } else {
                $auth = "no";
            }
        } else {
            $auth = "nonlog";
        }

        return $this->render('project/settings.html.twig', [
            'controller_name' => 'ProjectController',
            'session' => $session,
            'auth' => $auth,
            'form' => $form->createView(),
            'status' => $status,
            'project' => $project,
            'fileError' => $fileError
        ]);
    }
}

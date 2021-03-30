<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\ColorTheme\ColorTheme;
use App\Entity\Event;
use App\Entity\Follow;
use App\Entity\Media;
use App\Entity\Member;
use App\Entity\Post;
use App\Entity\Project;
use App\Entity\ProjectAdmin;
use App\Entity\Seen;
use App\Entity\User;
use App\Form\AddPostType;
use App\Form\EditPostType;
use App\Form\FollowType;
use App\Form\MemberType;
use App\Form\NewEventType;
use App\Form\NewProjectType;
use App\Form\ProjectSettingsType;
use App\Form\UnfollowType;
use App\Form\UnmemberType;
use App\ImageCrop\ImageCrop;
use App\ProjectCheck\ProjectCheck;
use App\Repository\EventRepository;
use App\Repository\FollowRepository;
use App\Repository\MediaRepository;
use App\Repository\MemberRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectAdminRepository;
use App\Repository\ProjectRepository;
use App\Repository\SeenRepository;
use App\Repository\UserRepository;
use App\ValidateImage\ValidateImage;
use DateTime;
use DateTimeZone;
use PhpParser\Node\Expr\AssignOp\Pow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\ImageValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/projekt", name="project.")
 */
class ProjectController extends AbstractController
{
    private $session;

    /**
     * @Route("/editPost", name="editPost", methods={"POST"})
     */
    public function editPost(Request $request, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $data = $request->request->get('data');
            $text = $data[0];
            $privacy = $data[1];
            $id = $data[2];

            $post = $postRepository->find($id);

            $result = "";
            if ($post->getPrivacy() == $privacy && $post->getText() == $text) {
                $result = "nochange";
            }

            if ($post->getText() != $text) {
                $post->setText($text);
                $result = "success";
            }

            if ($post->getPrivacy() != $privacy) {
                $post->setPrivacy($privacy);
                $result = "success";
            }

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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    /**
     * @Route("/deletePost", name="deletePost", methods={"POST"})
     */
    public function deletePost(Request $request, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $data = $request->request->get('data');
            $id = $data;

            $post = $postRepository->find($id);

            $result = "";
            if (!$post->getDeleted()) {
                $post->setDeleted(true);
                $result = "success";
            } else {
                $result = "fail";
            }

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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    /**
     * @Route("/editEvent", name="editEvent", methods={"POST"})
     */
    public function editEvent(Request $request, EventRepository $eventRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $name = $request->request->get('name');
            $location = $request->request->get('location');
            $start = date_create($request->request->get('start'));
            $end = date_create($request->request->get('end'));
            $description = $request->request->get('description');
            $privacy = $request->request->get('privacy');

            $event = $eventRepository->find($id);

            $result = "";

            if ($event->getPrivacy() == $privacy && $event->getName() == $name && $event->getLocation() == $location && $event->getStart() == $start && $event->getEnd() == $end && $event->getDescription() == $description) {
                $result = "nochange";
            } else {
                if ($event->getName() != $name) {
                    $event->setName($name);
                }

                if ($event->getPrivacy() != $privacy) {
                    $event->setPrivacy($privacy);
                }

                if ($event->getLocation() != $location) {
                    $event->setLocation($location);
                }

                if ($event->getStart() != $start) {
                    $event->setStart($start);
                }

                if ($event->getEnd() != $end) {
                    $event->setEnd($end);
                }

                if ($event->getDescription() != $description) {
                    $event->setDescription($description);
                }

                if (!$em = $this->getDoctrine()->getManager()->flush()) {
                    $result = "success";
                } else {
                    $result = "dbfail";
                }
            }

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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    /**
     * @Route("/deleteEvent", name="deleteEvent", methods={"POST"})
     */
    public function deleteEvent(Request $request, EventRepository $eventRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');

            $event = $eventRepository->find($id);

            $result = "";

            if (!$event) {
                $result = "notexist";
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->remove($event);

                if (!$em->flush()) {
                    $result = "success";
                } else {
                    $result = "dbfail";
                }
            }

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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    /**
     * @Route("/follow", name="follow", methods={"POST"})
     */
    public function follow(Request $request, ProjectRepository $projectRepository, SessionInterface $session, UserRepository $userRepository, FollowRepository $followRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $projectid = $request->request->get('project');
            $project = $projectRepository->find($projectid);
            $user = $userRepository->find($session->get('id'));
            $oldFollow = $followRepository->findOneBy(['follower' => $user, 'project' => $project]);
            $result = "";
            $em = $this->getDoctrine()->getManager();

            if (!$oldFollow) {
                $newFollow = new Follow();
                $newFollow->setFollower($user);
                $newFollow->setProject($project);

                $em->persist($newFollow);
                if (!$em->flush()) {
                    $result = "followsuccess";
                } else {
                    $result = "followfail";
                }
            } else {
                $em->remove($oldFollow);
                if (!$em->flush()) {
                    $result = "unfollowsuccess";
                } else {
                    $result = "unfollowfail";
                }
            }


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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

    /**
     * @Route("/member", name="member", methods={"POST"})
     */
    public function member(Request $request, ProjectRepository $projectRepository, SessionInterface $session, UserRepository $userRepository, MemberRepository $memberRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $projectid = $request->request->get('project');
            $project = $projectRepository->find($projectid);
            $user = $userRepository->find($session->get('id'));
            $oldMember = $memberRepository->findOneBy(['member' => $user, 'project' => $project]);
            $result = "";
            $em = $this->getDoctrine()->getManager();

            if (!$oldMember) {
                $newMember = new Member();
                $newMember->setMember($user);
                $newMember->setProject($project);
                $newMember->setAccepted(false);

                $em->persist($newMember);
                if (!$em->flush()) {
                    $result = "membersuccess";
                } else {
                    $result = "memberfail";
                }
            } else {
                $em->remove($oldMember);
                if (!$em->flush()) {
                    $result = "unmembersuccess";
                } else {
                    $result = "unmemberfail";
                }
            }


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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

    /**
     * @Route("/acceptMember", name="acceptMember", methods={"POST"})
     */
    public function acceptMember(Request $request, ProjectRepository $projectRepository, SessionInterface $session, MemberRepository $memberRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $projectid = $request->request->get('project');
            $project = $projectRepository->find($projectid);
            $memberID = $request->request->get('member');
            $member = $memberRepository->findOneBy(['id' => $memberID, 'project' => $project]);
            $result = "";
            $em = $this->getDoctrine()->getManager();

            $type = $request->request->get('type');

            if ($type == "accept") {
                $member->setAccepted(true);
                if (!$em->flush()) {
                    $result = "accept-success";
                } else {
                    $result = "accpet-fail";
                }
            } else if ($type == "decline") {
                $em->remove($member);
                if (!$em->flush()) {
                    $result = "decline-success";
                } else {
                    $result = "decline-fail";
                }
            }

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
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

    /**
     * @Route("/deleteMember", name="deleteMember", methods={"POST"})
     */
    public function deleteMember(Request $request, ProjectRepository $projectRepository, SessionInterface $session, MemberRepository $memberRepository, UserRepository $userRepository, ProjectAdminRepository $projectAdminRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $memberID = $request->request->get('member');
            $projectid = $request->request->get('project');
            $project = $projectRepository->find((int)$projectid);
            $user = $userRepository->find($memberID);
            $member = $memberRepository->findOneBy(['member' => $user, 'project' => $project]);
            $admin = $userRepository->find($session->get('id'));
            $admins = $projectAdminRepository->findBy(['project' => $project]);

            $result = "";

            if (in_array($admin, $admins) || $project->getAdmin() == $admin) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($member);
                if (!$em->flush()) {
                    $result = "success";
                } else {
                    $result = "dbfail";
                }
            } else {
                $result = "nonadmin";
            }

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

            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

    /**
     * @Route("/deleteHero", name="deleteHero", methods={"POST"})
     */
    public function deleteHero(Request $request, ProjectRepository $projectRepository, SessionInterface $session, MemberRepository $memberRepository, UserRepository $userRepository, ProjectAdminRepository $projectAdminRepository, MediaRepository $mediaRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $heroid = $request->request->get('id');
            $hero = $mediaRepository->find($heroid);
            $projectid = $request->request->get('project');
            $project = $projectRepository->find($projectid);
            $admin = $userRepository->find($session->get('id'));
            $admins = $projectAdminRepository->findBy(['project' => $project]);

            $result = "";

            if (in_array($admin, $admins) || $project->getAdmin() == $admin) {
                $em = $this->getDoctrine()->getManager();
                if (file_exists($this->getParameter('media') . $hero->getName())) {
                    unlink($this->getParameter('media') . '/' . $hero->getName());
                }
                $em->remove($hero);
                if (!$em->flush()) {
                    $result = "success";
                } else {
                    $result = "dbfail";
                }
            } else {
                $result = "nonadmin";
            }

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

            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

    /**
     * @Route("/getPostSeens", name="getPostSeens", methods={"POST"})
     */
    public function getPostSeens(Request $request, PostRepository $postRepository, SessionInterface $session, SeenRepository $seenRepository, UserRepository $userRepository, ProjectAdminRepository $projectAdminRepository, MediaRepository $mediaRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $postid = $request->request->get('id');
            $post = $postRepository->find($postid);
            $result = [];
            $seens = $seenRepository->findBy(['post' => $post]);
            foreach ($seens as $seen) {
                array_push($result, $seen->getUser()->getFirstname() . " " . $seen->getUser()->getLastname());
            }


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

            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => 'error',
            'message' => 'Not an AJAX request'
        ], 401);
    }

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
    public function index($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, FollowRepository $followRepository, Request $request, MemberRepository $memberRepository, PostRepository $postRepository, SeenRepository $seenRepository, ValidatorInterface $validator): Response
    {
        $this->session = $session;
        $project = $projectRepository->findOneBy(['id' => $id]);

        if ($project) {
            $projectCheck = new ProjectCheck($project->getId(), $projectRepository);
            if ($projectCheck->isAccessible()) {
                $color = new ColorTheme();
                $palette = $color->colorPallette($project->getColor());

                $followBtn = "";
                $memberBtn = "";

                $projectAdminsArray = $this->getDoctrine()->getRepository(ProjectAdmin::class)->findBy(['project' => $project]);
                $projectAdmins = [];
                foreach ($projectAdminsArray as $admin) {
                    array_push($projectAdmins, $admin->getUser()->getId());
                }

                $projectMembersArray = $this->getDoctrine()->getRepository(Member::class)->findBy(['project' => $project]);

                $projectMembers = [];
                foreach ($projectMembersArray as $member) {
                    if ($member->getAccepted()) {
                        array_push($projectMembers, $member->getMember()->getId());
                    }
                }


                if ($session->get('username') != "") {
                    $user = $userRepository->find($session->get('id'));

                    if (!$followRepository->findOneBy(['follower' => $session->get('id'), 'project' => $project])) {
                        //$followForm = $this->createForm(FollowType::class, $follow);
                        $followBtn = "follow";
                    } else {
                        //$followForm = $this->createForm(UnfollowType::class, $follow);
                        $followBtn = "unfollow";
                    }

                    if (!$memberRepository->findOneBy(['member' => $user, 'project' => $project])) {


                        //$memberForm = $this->createForm(MemberType::class, $newMember);
                        $memberBtn = "member";
                    } else {
                        //$memberForm = $this->createForm(UnmemberType::class, $newMember);
                        $memberBtn = "unmember";
                    }

                    $settingsForm = $this->createForm(ProjectSettingsType::class, $project);
                    $fileError = "";
                    $mediaError = "";
                    $settingsForm->handleRequest($request);
                    if ($settingsForm->isSubmitted()) {

                        // Zpracování profilového obrázku
                        $file = $request->files->get('project_settings')["attach"];
                        if ($file) {
                            $img = new ImageCrop($file, $this->getParameter('project_pic'), $this->getDoctrine()->getManager());
                            if ($img->cropProjectImage($project)) {
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
                        if (count($validator->validate($post)) > 0) {
                            //! ERROR
                        } else {
                            $em = $this->getDoctrine()->getManager();
                            $media = $request->files->get('add_post')['media'];
                            if ($media) {
                                $imgValidator = new ValidateImage();
                                /** @var UploadedFile $file */
                                foreach ($media as $file) {
                                    if ($imgValidator->isImgValid($file) == "success") {
                                        $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                                        $file->move(
                                            $this->getParameter('media'),
                                            $filename
                                        );
                                        $newMedia = new Media();
                                        $newMedia->setName($filename);
                                        $newMedia->setProject($project);
                                        $newMedia->setType('post');
                                        $newMedia->setPost($post);
                                        $newMedia->setUploader($user);
                                        $em->persist($newMedia);
                                    } elseif ($imgValidator->isImgValid($file) == "badext") {
                                        $fileError = "badext";
                                    } elseif ($imgValidator->isImgValid($file) == "toobig") {
                                        $fileError = "toobig";
                                    } elseif ($imgValidator->isImgValid($file) == "badsize") {
                                        $fileError = "badsize";
                                    }
                                }
                            }

                            $em->persist($post);
                            $em->flush();
                            unset($post);
                            unset($addPostForm);
                            $post = new Post();
                            $addPostForm = $this->createForm(AddPostType::class, $post);
                        }
                    }

                    $posts = $project->getPosts();

                    // SEEN
                    date_default_timezone_set('Europe/Prague');
                    $seenPosts = $postRepository->findBy(['project' => $project]);
                    foreach ($seenPosts as $post) {
                        if (!$seenRepository->findOneBy(['user' => $user, 'post' => $post])) {
                            $seen = new Seen();
                            $seen->setPost($post);
                            $seen->setUser($user);
                            $seen->setDate(DateTime::createFromFormat('Y-m-d', date('Y-m-d')));

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($seen);
                            $em->flush();
                        }
                    }

                    // EVENTS

                    $event = new Event();
                    $event->setProject($project);
                    $event->setAdmin($user);
                    $event->setCreated(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                    $newEventForm = $this->createForm(NewEventType::class, $event);
                    $newEventForm->handleRequest($request);
                    if ($newEventForm->isSubmitted()) {
                        $end = $newEventForm->getData()->getEnd();
                        $start = $newEventForm->getData()->getStart();
                        if ($start < $end) {
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($event);
                            $em->flush();
                        } else {
                            $newEventResult = "endbeforestart";
                        }
                    }
                } else {
                }

                if ($followBtn != "") {
                    return $this->render('project/index.html.twig', [
                        'controller_name' => 'ProjectController',
                        'session' => $session,
                        'project' => $project,
                        'followBtn' => $followBtn,
                        'palette' => $palette,
                        'pic' => $this->getParameter('project_pic'),
                        'postForm' => $addPostForm->createView(),
                        'projectSettingsForm' => $settingsForm->createView(),
                        'mediaError' => $mediaError,
                        'posts' => $posts,
                        'projectAdmins' => $projectAdmins,
                        'projectMembers' => $projectMembers,
                        'memberBtn' => $memberBtn,
                        'newEventForm' => $newEventForm->createView()
                    ]);
                } else {
                    return $this->render('project/index.html.twig', [
                        'controller_name' => 'ProjectController',
                        'session' => $session,
                        'project' => $project,
                        'palette' => $palette,
                        'projectAdmins' => $projectAdmins,
                        'projectMembers' => $projectMembers
                    ]);
                }
            }
        }

        return $this->render('error/404.html.twig', [
            'controller_name' => 'ProjectController',
            'session' => $session
        ], new Response('', 404));
    }
}

<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\ColorTheme\ColorTheme;
use App\Entity\Event;
use App\Entity\Follow;
use App\Entity\Media;
use App\Entity\Member;
use App\Entity\Post;
use App\Entity\ProjectAdmin;
use App\Entity\Seen;
use App\Form\AddPostType;
use App\Form\NewEventType;
use App\Form\ProjectSettingsType;
use App\ImageCrop\ImageCrop;
use App\Memberships\Memberships;
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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/projekt", name="project.")
 */
class ProjectController extends AbstractController
{
    // JSON Endpoint pro úpravu příspěvků

    /**
     * @Route("/editPost", name="editPost", methods={"POST"})
     */
    public function editPost(Request $request, PostRepository $postRepository)
    {
        // Kontrola, jestli je zvolaný request AJAXem
        if ($request->isXmlHttpRequest()) {
            // Získání dat z requestu
            $data = $request->request->get('data');
            $text = $data[0];
            $privacy = $data[1];
            $id = $data[2];

            $post = $postRepository->find($id);
            // Zpracování úpravy
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
            // Odeslání do DB
            $this->getDoctrine()->getManager()->flush();


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
            // Serializace a odeslání odpovědi
            $serializer = new Serializer($normalizers, $encoders);
            $response = $serializer->serialize($result, 'json');
            return new JsonResponse($response, 200, [], true);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ]);
    }

    // JSON Endpoint pro mazání příspěvků

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

            $this->getDoctrine()->getManager()->flush();


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

    // JSON Endpoint pro úpravu událostí

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

            if ($privacy == "false") {
                $privacy = false;
            } elseif ($privacy == "true") {
                $privacy = true;
            }

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

    // JSON Endpoint pro mazání událostí

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

    // JSON Endpoint pro sledování

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
                $newFollow->setFollowed(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

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

    // JSON Endpoint pro žádost o členství

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
                $newMember->setRequestDate(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

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

    // JSON Endpoint pro přijetí, nebo odmítnutí žádosti o členství

    /**
     * @Route("/acceptMember", name="acceptMember", methods={"POST"})
     */
    public function acceptMember(Request $request, ProjectRepository $projectRepository, MemberRepository $memberRepository)
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
                $member->setAcceptedDate(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
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

    // JSON Endpoint pro odebrání členství

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
            $loggedUser = $userRepository->find($session->get('id'));
            $admin = $projectAdminRepository->findOneBy(['user' => $loggedUser, 'project' => $project]);
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

    // JSON Endpoint pro mazání obrázků v pozadí

    /**
     * @Route("/deleteHero", name="deleteHero", methods={"POST"})
     */
    public function deleteHero(Request $request, ProjectRepository $projectRepository, SessionInterface $session, UserRepository $userRepository, ProjectAdminRepository $projectAdminRepository, MediaRepository $mediaRepository)
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

    // JSON Endpoint pro Získání všech zhládnutí příspěvku

    /**
     * @Route("/getPostSeens", name="getPostSeens", methods={"POST"})
     */
    public function getPostSeens(Request $request, PostRepository $postRepository, SeenRepository $seenRepository)
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

    // JSON Endpoint pro vypsání projektu

    /**
     * @Route("/{id}", name="project")
     */
    public function index($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, FollowRepository $followRepository, Request $request, MemberRepository $memberRepository, PostRepository $postRepository, SeenRepository $seenRepository, ValidatorInterface $validator, MailerInterface $mailer, MediaRepository $mediaRepository): Response
    {
        $this->session = $session;
        $project = $projectRepository->find($id); // Získání projektu z DB

        // Kontrola, jestli je projekt v DB
        if ($project) {
            // Kontrlova projektu
            $projectCheck = new ProjectCheck($project->getId(), $projectRepository);
            if ($projectCheck->isAccessible()) {
                // Zavolání služby pro získání berevné palety projektu
                $color = new ColorTheme();
                $palette = $color->colorPallette($project->getColor());

                $imgValidator = new ValidateImage();    // Validuje obrázek
                $memberships = new Memberships();       //Kontroluje členství uživatele
                $postsPrivacy = 0;
                $em = $this->getDoctrine()->getManager();       // Zavolání entity managera

                $errors = [];   // pole pro errory

                $followBtn = "";
                $memberBtn = "";

                // Vyhledání všech administrátorů projektu
                $projectAdminsArray = $this->getDoctrine()->getRepository(ProjectAdmin::class)->findBy(['project' => $project]);
                $projectAdmins = [];
                foreach ($projectAdminsArray as $admin) {
                    array_push($projectAdmins, $admin->getUser()->getId());
                }

                // Vyhledání všech členů projektu
                $projectMembersArray = $memberRepository->findBy(['project' => $project]);

                $projectMembers = [];
                foreach ($projectMembersArray as $member) {
                    if ($member->getAccepted()) {
                        array_push($projectMembers, $member->getMember()->getId());
                    }
                }

                $posts = [];

                // Pokud je uživatel přihlášený
                if ($session->get('username') != "") {
                    $user = $userRepository->find($session->get('id'));
                    // Kontroluje členství uživatele
                    if ($memberships->isUserMember($project, $user)) {
                        $postsPrivacy = 1;
                    } else {
                        $postsPrivacy = 0;
                    }

                    // Vytvoření tlačítka pro sledování
                    if (!$followRepository->findOneBy(['follower' => $session->get('id'), 'project' => $project])) {
                        $followBtn = "follow";
                    } else {
                        $followBtn = "unfollow";
                    }

                    // Vytvoření tlačítka pro členství
                    if (!$memberRepository->findOneBy(['member' => $user, 'project' => $project])) {
                        $memberBtn = "member";
                    } else {
                        $memberBtn = "unmember";
                    }

                    // form s nastavením projektu
                    $settingsForm = $this->createForm(ProjectSettingsType::class, $project);
                    $fileError = "";
                    $settingsForm->handleRequest($request);
                    if ($settingsForm->isSubmitted()) {
                        // Zpracování profilového obrázku
                        $file = $request->files->get('project_settings')["attach"];
                        if ($file) {
                            $validation = $imgValidator->isImgValid($file);
                            if ($validation == "success") {
                                $img = new ImageCrop($file, $this->getParameter('project_pic'), $this->getDoctrine()->getManager());
                                if ($img->cropProjectImage($project)) {
                                }
                            } elseif ($validation == "badsize") {
                                array_push($errors, 'Nový profilový obrázek má moc veliké rozměry');
                            } elseif ($validation == "toobig") {
                                array_push($errors, 'Nový profilový obrázek je moc velký (maximální velikost 2 MB)');
                            } elseif ($validation == "badext") {
                                array_push($errors, 'Nový profilový obrázek je nepodporovaného typu (povolené jsou jen .png, .jpg a .jpeg)');
                            }
                            $fileError = "errr";
                        }

                        // Zpracování obrázků v pozadí
                        $media = $request->files->get('project_settings')["medias"];
                        if ($media) {
                            /** @var UploadedFile $file*/
                            foreach ($media as $file) {
                                $validation = $imgValidator->isImgValid($file);
                                if ($validation == "success") {
                                    $img = new ImageCrop($file, $this->getParameter('media'), $this->getDoctrine()->getManager());
                                    if ($img->scaleBgImage($project, $user)) {
                                    }
                                } elseif ($validation == "badsize") {
                                    array_push($errors, 'Nový obrázek má moc veliké rozměry');
                                } elseif ($validation == "toobig") {
                                    array_push($errors, 'Nový obrázek je moc velký (maximální velikost 2 MB)');
                                } elseif ($validation == "badext") {
                                    array_push($errors, 'Nový obrázek je nepodporovaného typu (povolené jsou jen .png, .jpg a .jpeg)');
                                }
                            }
                        }

                        // Mazání obrázků v pozadí

                        $heroImages = $mediaRepository->findBy(['type' => 'hero', 'project' => $project]);
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
                                }
                                $em->remove($heroImagesArray[$i]);
                            }
                        }

                        // Pokud se vše podaří, zapíše do DB a refreshne stránku
                        if (empty($errors)) {
                            $em->flush();
                            return $this->redirect($request->getUri());
                        }
                    }

                    // Zpracování nového příspěvku
                    $post = new Post();
                    $post->setProject($project);
                    $post->setAuthor($user);
                    $post->setPostedDate(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                    $addPostForm = $this->createForm(AddPostType::class, $post);

                    $addPostForm->handleRequest($request);
                    if ($addPostForm->isSubmitted()) {
                        if (count($validator->validate($post)) > 0) {
                            array_push($errors, 'Došlo k chybě. Pravděpodobně je váš příspěvek delší než 1000 znaků');
                        } else {
                            $media = $request->files->get('add_post')['media'];
                            if ($media) {
                                /** @var UploadedFile $file */
                                foreach ($media as $file) {
                                    $validation = $imgValidator->isImgValid($file);
                                    if ($validation == "success") {
                                        $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

                                        $newMedia = new Media();
                                        $newMedia->setName($filename);
                                        $newMedia->setProject($project);
                                        $newMedia->setType('post');
                                        $newMedia->setPost($post);
                                        $newMedia->setUploader($user);
                                        $newMedia->setUploadDate(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
                                        $em->persist($newMedia);
                                        $file->move(
                                            $this->getParameter('media'),
                                            $filename
                                        );
                                    } elseif ($validation == "badsize") {
                                        array_push($errors, 'Vámi nahrávaný obrázek má moc veliké rozměry');
                                    } elseif ($validation == "toobig") {
                                        array_push($errors, 'Vámi nahrávaný obrázek je moc velký (maximální velikost 2 MB)');
                                    } elseif ($validation == "badext") {
                                        array_push($errors, 'Vámi nahrávaný obrázek je nepodporovaného typu (povolené jsou jen .png, .jpg a .jpeg). Také se mohlo stát, že je váš obrázek poškozený.');
                                    }
                                }
                            }

                            // Pokud nenasla žádná chyba, zapíše do DB a refreshne stránku
                            if (empty($errors)) {
                                $em->persist($post);
                                $em->flush();
                            }

                            unset($post);
                            unset($addPostForm);
                            $post = new Post();
                            $addPostForm = $this->createForm(AddPostType::class, $post);
                            if (empty($errors)) {
                                return $this->redirect($request->getUri());
                            }
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

                            // Email notifikace

                            if (isset($request->request->get('new_event')['notification'])) {
                                $emails = [];
                                foreach ($project->getMembers() as $member) {
                                    array_push($emails, new Address($member->getMember()->getUsername() . '@ms.spsostrov.cz', $member->getMember()->getFirstname() . ' ' . $member->getMember()->getLastname()));
                                }
                                // new Address('ruzema@ms.spsostrov.cz', 'Martin Růžek')
                                $email = (new TemplatedEmail())
                                    ->bcc(...$emails)
                                    ->subject('Nová událost')
                                    ->htmlTemplate('email/newevent.html.twig')
                                    ->context([
                                        'event' => $event
                                    ]);

                                if (!$mailer->send($email)) {
                                }
                            }

                            unset($event);
                            unset($newEventForm);
                            $event = new Event();
                            $newEventForm = $this->createForm(NewEventType::class, $event);
                        } else {
                            array_push($errors, 'Vámi zadaný konec akce je před jejím začátkem.');
                        }
                    }
                } else {
                }

                // Příspěvky k zobrazení

                $resultsPerPage = 15;
                $numberOfPosts = count($postRepository->findNonDeleted($project, $postsPrivacy));
                $numberOfPages = ceil($numberOfPosts / $resultsPerPage);
                if (!$request->query->get('postpage')) {
                    $page = 1;
                } else {
                    if (is_numeric($request->query->get('postpage')) && $request->query->get('postpage') <= $numberOfPages && $request->query->get('postpage') >= 1) {
                        $page = (int)$request->query->get('postpage');
                    } else {
                        $page = 1;
                    }
                }
                $thisPageFirstPost = ($page - 1) * $resultsPerPage;

                $posts = $postRepository->findPostFromTo($thisPageFirstPost, $resultsPerPage, $postsPrivacy, $project);

                if ($page < 4) {
                    $currentmin = 1;
                } else {
                    $currentmin = $page - 3;
                }

                if ($page + 3 >= $numberOfPages) {
                    $currentmax = $numberOfPages;
                } else {
                    $currentmax = $page + 3;
                }

                $postPages = [
                    'max' => $numberOfPages,
                    'current' => $page,
                    'currentmin' => $currentmin,
                    'currentmax' => $currentmax
                ];

                // Odeslání zobrazení
                if ($session->get('username') != null) {
                    date_default_timezone_set('Europe/Prague');
                    $seenPosts = $postRepository->findBy(['project' => $project]);
                    foreach ($seenPosts as $post) {
                        if (!$seenRepository->findOneBy(['user' => $user, 'post' => $post])) {
                            $seen = new Seen();
                            $seen->setPost($post);
                            $seen->setUser($user);
                            $seen->setDate(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($seen);
                            $em->flush();
                        }
                    }
                }

                // Media k zobrazení

                $allMedias = $mediaRepository->findProjectMedia($project);
                $medias = [];

                foreach ($allMedias as $media) {
                    if ($media->getPost()->getDeleted() == 0) {
                        if ($postsPrivacy == 1) {
                            array_push($medias, $media);
                        } else {
                            if ($media->getPost()->getPrivacy() == 0) {
                                array_push($medias, $media);
                            }
                        }
                    }
                }

                // Render Twigem
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
                        'posts' => $posts,
                        'projectAdmins' => $projectAdmins,
                        'projectMembers' => $projectMembers,
                        'memberBtn' => $memberBtn,
                        'newEventForm' => $newEventForm->createView(),
                        'errors' => $errors,
                        'postPages' => $postPages,
                        'medias' => $medias
                    ]);
                } else {
                    return $this->render('project/index.html.twig', [
                        'controller_name' => 'ProjectController',
                        'session' => $session,
                        'project' => $project,
                        'palette' => $palette,
                        'projectAdmins' => $projectAdmins,
                        'projectMembers' => $projectMembers,
                        'errors' => $errors,
                        'posts' => $posts,
                        'postPages' => $postPages,
                        'medias' => $medias
                    ]);
                }
            }
        }

        return new Response('', 404);
    }
}

<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\Entity\IndexBlock;
use App\Entity\Media;
use App\Entity\Member;
use App\Entity\Project;
use App\Entity\ProjectAdmin;
use App\Form\NewAbsAdminType;
use App\Form\NewProjectType;
use App\Form\ProjectSettingsType;
use App\ImageCrop\ImageCrop;
use App\Repository\IndexBlockRepository;
use App\Repository\PostRepository;
use App\Repository\ProjectAdminRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Controller pro administrátorské prostředí

/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    private $auth;

    // konstruktor

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        // Service pro ověření uživatele
        $this->auth = new Authentication($session, $userRepository);
    }

    // Hlavní stránka admin. prostření

    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request, IndexBlockRepository $indexBlockRepository): Response
    {
        // ověření, zda je uživ. admin
        if ($this->auth->isAbsAdmin()) {
            $em = $this->getDoctrine()->getManager();
            // Získání index bloků
            $allProjects = $projectRepository->findAll();
            $allIndexBlocksArray = $indexBlockRepository->findAll();
            $projectIndexBlocks = [];
            $postIndexBlocks = [];
            foreach ($allIndexBlocksArray as $block) {
                if ($block->getType() == "project") {
                    array_push($projectIndexBlocks, $block->getProject()->getId());
                }
            }
            $allUsers = $userRepository->findAll();

            // Přidání nového abs. správce
            $newAdminForm = $this->createForm(NewAbsAdminType::class);
            $newAdminForm->handleRequest($request);
            if ($newAdminForm->isSubmitted()) {
                $selected = $newAdminForm->getData()['role'];

                $newAdminUsername = explode('(', $selected);
                $newAdminUsername = explode(',', $newAdminUsername[1]);
                $newAdminUsername = $newAdminUsername[0];

                $newAdminUser = $userRepository->findOneBy(['username' => trim($newAdminUsername)]);

                if ($newAdminUser->getRole() == "admin") {
                } else {
                    $newAdminUser->setRole("admin");

                    $em->flush();
                }
            }

            return $this->render('admin/index.html.twig', [
                'session' => $session,
                'allUsers' => $allUsers,
                'allProjects' => $allProjects,
                'projectIndexBlocks' => $projectIndexBlocks,
                'postIndexBlocks' => $postIndexBlocks,
                'allIndexBlocksArray' => $allIndexBlocksArray,
                'newAdminForm' => $newAdminForm->createView()
            ]);
        }

        return new Response('', 401);
    }

    // Výpis všech projektů


    /**
     * @Route("/projekty", name="projects")
     */
    public function projects(SessionInterface $session, ProjectRepository $projectRepository)
    {
        // Získání všech projektů k výpisu

        if ($this->auth->isAbsAdmin()) {
            $projects = $projectRepository->findAll();

            return $this->render('admin/projects.html.twig', [
                'session' => $session,
                'projects' => $projects
            ]);
        }

        return new Response('', 401);
    }

    // Vápis jednotlivých projeků

    /**
     * @Route("/projekt/{id}", name="project")
     */
    public function project($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request, ProjectAdminRepository $projectAdminRepository)
    {
        // Nastevení projektu
        $project = $projectRepository->find($id);
        if ($this->auth->isAbsAdmin() && $project != null) {

            $user = $userRepository->find($session->get('id'));
            $basicSettingsForm = $this->createForm(ProjectSettingsType::class, $project);

            // Zavolání entitty managera
            $em = $this->getDoctrine()->getManager();

            $status = "";
            // Základní nastevení
            $basicSettingsForm->handleRequest($request);
            if ($basicSettingsForm->isSubmitted()) {
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
                        $status = "badext";
                    }
                }

                // Zpracování obrázků v pozadí
                $media = $request->files->get('project_settings')["medias"];
                if ($media) {
                    /** @var UploadedFile $file*/
                    foreach ($media as $file) {
                        $ext = $file->guessClientExtension();
                        if ($ext == "jpeg" || $ext == "jpg" || $ext == "png") {
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
                                $status = "badheight";
                            }
                        } else {
                            $status = "badext";
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
                        }
                        $em->remove($heroImagesArray[$i]);
                    }
                }

                // Pokud se vše podaří, zapíše do DB a refreshne stránku
                if ($status == "") {
                    $em->flush();
                    $status = "success";
                    return $this->redirect($request->getUri());
                }
            }

            // Nový admin projetk

            $newAdmin = new ProjectAdmin();
            $newAdmin->setProject($project);
            $newAdmin->setDateAdded(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
            $addAdminForm = $this->createFormBuilder()
                ->add('user', TextType::class, [
                    'attr' => [
                        'class' => 'addProjectAdmin form-control'
                    ],
                    'label' => 'Hledat uživatele',
                    'label_attr' => [
                        'class' => 'form-label'
                    ]
                ])
                ->add('submit', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-success'
                    ],
                    'label' => 'Přidat'
                ])->getForm();

            $error = "";
            $addAdminForm->handleRequest($request);
            if ($addAdminForm->isSubmitted()) {
                $userdata = $addAdminForm->getData();
                explode('(', $userdata['user']);
                $newAdminUsername = explode('(', $userdata['user']);
                $newAdminUsername = explode(',', $newAdminUsername[1]);
                $newAdminUsername = $newAdminUsername[0];
                $newAdminUser = $userRepository->findOneBy(['username' => trim($newAdminUsername)]);
                if (!$projectAdminRepository->findOneBy(['user' => $newAdminUser, 'project' => $project])) {
                    $newAdmin->setUser($newAdminUser);
                    $newMember = new Member;
                    $newMember->setAccepted(true);
                    $newMember->setMember($newAdminUser);
                    $newMember->setProject($project);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newAdmin);
                    $em->persist($newMember);
                    $em->flush();
                    $status = "success";
                    return $this->redirect($request->getUri());
                } else {
                    $status = "alreadyadmin";
                }
            }

            return $this->render('admin/project.html.twig', [
                'session' => $session,
                'project' => $project,
                'basicSettingsForm' => $basicSettingsForm->createView(),
                'status' => $status,
                'newAdminForm' => $addAdminForm->createView(),
            ]);
        } else if ($project == null) {
            return $this->render('error/404.html.twig', [
                'session' => $session
            ], new Response('', 404));
        }

        return new Response('', 401);
    }

    // Vytváření nového projektu

    /**
     * @Route("/novyprojekt", name="newProject")
     */
    public function newProject(SessionInterface $session, UserRepository $userRepository, Request $request)
    {
        // Vytváření nového projetku
        if ($this->auth->isAbsAdmin()) {
            $project = new Project();
            $form = $this->createForm(NewProjectType::class, $project);

            $formResponse = "";
            $id = "";
            $fileError = "";
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                // Zpracování nahraného obrázku
                /** @var UploadedFile $file*/
                $file = $request->files->get('new_project')['attach'];
                if ($file) {
                    $img = new ImageCrop($file, $this->getParameter('project_pic'), $this->getDoctrine()->getManager());
                    $img->cropProjectImage($project, "new");
                }

                $userdata = $request->request->get('new_project')['mainAdmin'];

                // Rozparování našeprávače
                $newAdminUsername = explode('(', $userdata);
                $newAdminUsername = explode(',', $newAdminUsername[1]);
                $newAdminUsername = $newAdminUsername[0];

                $newAdmin = $userRepository->findOneBy(['username' => $newAdminUsername]);
                $project->setAdmin($newAdmin);
                $project->setCreated(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                // Vytvoření členství pro admina
                $member = new Member();
                $member->setProject($project);
                $member->setMember($newAdmin);
                $member->setAccepted(true);

                if (!$fileError) {
                    // Zapsání do DB
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($project);
                    $em->persist($member);
                    $em->flush();
                    $formResponse = "success";
                    $id = $project->getId();

                    // Přesměronání na nově vytvořený projekt
                    return $this->redirectToRoute('project.project', ['id' => $project->getId()]);
                }
            }

            return $this->render("admin/newproject.html.twig", [
                'session' => $session,
                'newProjectForm' => $form->createView()
            ]);
        }

        return new Response('', 401);
    }

    // JSON Endpoint pro získání uživatelů

    /**
     * @Route("/getPossibleAdmins", name="getPossibleAdmins", methods={"POST"})
     */
    public function getPossibleAdmins(Request $request, UserRepository $userRepository, ProjectAdminRepository $projectAdminRepository, ProjectRepository $projectRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $input = $request->request->get('input');
            $usersAll = $userRepository->searchUser($input);
            $projectID = $request->request->get('project');
            $users = [];
            if ($projectID != null) {
                $project = $projectRepository->find($projectID);

                foreach ($usersAll as $user) {
                    if (!$projectAdminRepository->findOneBy(['user' => $user, 'project' => $project]) && $user != $project->getAdmin()) {
                        array_push($users, [
                            'firstname' => $user->getFirstname(),
                            'lastname' => $user->getLastname(),
                            'username' => $user->getUsername(),
                            'class' => $user->getClass()
                        ]);
                    }
                }
            } else {
                //$users = $usersAll;
                foreach ($usersAll as $user) {
                    array_push($users, [
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                        'username' => $user->getUsername(),
                        'class' => $user->getClass()
                    ]);
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
            $response = $serializer->serialize($users, 'json');
            return new JsonResponse($response, 200, [], true);
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro odstranění adminů z projektu

    /**
     * @Route("/delAdminFromProject", name="delAdminFromProject", methods={"POST", "GET"})
     */
    public function delAdminFromProject(Request $request, ProjectAdminRepository $projectAdminRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $data = $request->request->get('data');
            $project = $request->request->get('project');

            $admin = $projectAdminRepository->findOneBy(['user' => (int)$data, 'project' => (int)$project]);

            $em = $this->getDoctrine()->getManager();
            $em->remove($admin);
            if (!$em->flush()) {
                $result = "success";
            } else {
                $result = "fail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro odstranění projektu

    /**
     * @Route("/delProject", name="delProject", methods={"POST"})
     */
    public function delProject(Request $request, ProjectRepository $projectRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $data = $request->request->get('data');

            $project = $projectRepository->find($data);

            $result = "";
            if (!$project->getDeleted()) {
                $project->setDeleted(true);

                foreach ($project->getPosts() as $post) {
                    $post->setDeleted(true);
                }
                $result = "success";
            } else {
                $project->setDeleted(false);
                foreach ($project->getPosts() as $post) {
                    $post->setDeleted(false);
                }
                $result = "recovered";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro změnu hlavního admina projektu

    /**
     * @Route("/changeMainAdmin", name="changeMainAdmin", methods={"POST"})
     */
    public function changeMainAdmin(Request $request, ProjectRepository $projectRepository, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $input = $request->request->get('input');

            $project = $projectRepository->find($request->request->get('project'));

            $newAdminUsername = explode('(', $input);
            $newAdminUsername = explode(',', $newAdminUsername[1]);
            $newAdminUsername = $newAdminUsername[0];

            $newAdmin = $userRepository->findOneBy(['username' => $newAdminUsername]);
            $project->setAdmin($newAdmin);
            $result = "";

            if (!$em = $this->getDoctrine()->getManager()->flush()) {
                $result = "success";
            } else {
                $result = "fail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro hlednání všech uživatelů

    /**
     * @Route("/searchAllUsers", name="searchAllUsers", methods={"POST"})
     */
    public function searchAllUsers(Request $request, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $input = $request->request->get('input');
            $usersAll = $userRepository->searchUser($input);
            $results = [];

            foreach ($usersAll as $user) {
                $userArray = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'class' => $user->getClass(),
                    'tag' => $user->getTag(),
                    'firstLogin' => $user->getFirstLogin()
                ];


                array_push($results, $userArray);
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
            $response = $serializer->serialize($results, 'json');
            return new JsonResponse($response, 200, [], true);
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro úpravu uživatele

    /**
     * @Route("/editUser", name="editUser", methods={"POST"})
     */
    public function editUser(Request $request, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $id = $request->request->get('id');
            $type = $request->request->get('type');

            $user = $userRepository->find($id);
            $result = "";
            $em = $this->getDoctrine()->getManager();

            if ($type == "delimg") {
                if ($user->getImage() != 'default.png') {
                    unlink($this->getParameter('user_pic') . '/' . $user->getImage());
                    $user->setImage('default.png');

                    if (!$em->flush()) {
                        $result = "success";
                    }
                } else {
                    $result = "alreadydefault";
                }
            } else if ($type == "deldesc") {
                if ($user->getDescription() != '') {
                    $user->setDescription('');

                    if (!$em->flush()) {
                        $result = "success";
                    }
                } else {
                    $result = "alreadyclear";
                }
            } else {
                $result = "badrequest";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro odstranění uživatele

    /**
     * @Route("/delUser", name="delUser", methods={"POST"})
     */
    public function delUser(Request $request, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $id = $request->request->get('id');

            $user = $userRepository->find($id);
            $result = "";
            $em = $this->getDoctrine()->getManager();

            $em->remove($user);
            if (!$em->flush()) {
                $result = "success";
            } else {
                $result = "dbfail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro přidání nového bloku na hlavní stranu projektu

    /**
     * @Route("/addNewBlock", name="addNewBlock", methods={"POST"})
     */
    public function addNewBlock(Request $request, ProjectRepository $projectRepository, IndexBlockRepository $indexBlockRepository, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $id = $request->request->get('id');
            $type = $request->request->get('type');

            $result = "";
            if ($type == "project") {
                $project = $projectRepository->find($id);
                if (!$indexBlockRepository->findOneBy(['project' => $project])) {
                    $newBlock = new IndexBlock();
                    $newBlock->setProject($project);
                    $newBlock->setType("project");
                    $newBlock->setAdded(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newBlock);
                    if (!$em->flush()) {
                        $result = "success";
                    } else {
                        $result = "dbfail";
                    }
                } else {
                    $result = "inuse";
                }
            } else if ($type == "post") {
                $post = $postRepository->find($id);
                if (!$indexBlockRepository->findOneBy(['post' => $post])) {
                    $newBlock = new IndexBlock();
                    $newBlock->setPost($post);
                    $newBlock->setType("post");

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newBlock);
                    if (!$em->flush()) {
                        $result = "success";
                    } else {
                        $result = "dbfail";
                    }
                } else {
                    $result = "inusepost";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro odstranění index blocku

    /**
     * @Route("/deleteIndexBlock", name="deleteIndexBlock", methods={"POST"})
     */
    public function deleteIndexBlock(Request $request, IndexBlockRepository $indexBlockRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $id = $request->request->get('id');
            $ib = $indexBlockRepository->find($id);

            $result = "";
            if ($ib) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($ib);
                if (!$em->flush()) {
                    $result = "success";
                } else {
                    $result = "dberror";
                }
            } else {
                $result = "nonexistant";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro odstranění hlavního admina aplikace

    /**
     * @Route("/deleteAbsAdmin", name="deleteAbsAdmin", methods={"POST"})
     */
    public function deleteAbsAdmin(Request $request, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $id = $request->request->get('id');
            $result = "";
            $user = $userRepository->find($id);
            $allAdmins = $userRepository->findBy(['role' => 'admin']);
            if ($user->getRole() == "admin" && count($allAdmins) > 2) {
                $user->setRole('user');
                $em = $this->getDoctrine()->getManager();
                if (!$em->flush()) {
                    $result = "success";
                } else {
                    $result = "dbfail";
                }
            } else {
                $result = "toofewadmins";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro smazání contentu, který pro tuto akci byl označen v DB

    /**
     * @Route("/deleteAllContent", name="deleteAllContent", methods={"POST"})
     */
    public function deleteAllContent(Request $request, ProjectRepository $projectRepository, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $result = "";
            $em = $this->getDoctrine()->getManager();

            $projects = $projectRepository->findBy(['deleted' => true]);
            $delMedia = [];
            foreach ($projects as $project) {
                foreach ($project->getPosts() as $post) {
                    $post->setDeleted(true);
                }

                $em->remove($project);
            }

            $posts = $postRepository->findBy(['deleted' => true]);

            foreach ($posts as $post) {
                $medias = $post->getMedia();
                foreach ($medias as $media) {
                    if (file_exists($this->getParameter('media') . $media->getName())) {
                        unlink('img/media/' . $media->getName());
                    }
                    $em->remove($media);
                }
                $em->remove($post);
            }

            if (!$em->flush()) {
                $result = "success";
            } else {
                $result = "dbfail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro Obnovení příspěvků uživatele

    /**
     * @Route("/getUserDeletedPosts", name="getUserDeletedPosts", methods={"POST"})
     */
    public function getUserDeletedPosts(Request $request, UserRepository $userRepository, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $user = $userRepository->find($request->request->get('id'));
            $posts = $postRepository->findBy(['author' => $user, 'deleted' => true]);
            $result = [];
            foreach ($posts as $post) {
                if ($post->getProject()->getDeleted() == false) {
                    array_push($result, [
                        'id' => $post->getId(),
                        'text' => $post->getText(),
                        'project' => $post->getProject()->getName(),
                        'privacy' => $post->getPrivacy(),
                        'posted' => $post->getPostedDate()
                    ]);
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro obnovu příspěvku

    /**
     * @Route("/restorePost", name="restorePost", methods={"POST"})
     */
    public function restorePost(Request $request, PostRepository $postRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $post = $postRepository->find($request->request->get('id'));
            $result = "";

            $post->setDeleted(false);

            if (!$this->getDoctrine()->getManager()->flush()) {
                $result = "success";
            } else {
                $result = "dbfail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }

    // JSON Endpoint pro deaktivaci účtů

    /**
     * @Route("/deactivateUsers", name="deactivateUsers", methods={"POST"})
     */
    public function deactivateUsers(Request $request, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $users = $userRepository->findAll();

            foreach ($users as $user) {
                if (substr($user->getClass(), -1) == '4') {
                    $user->setDeactivated(true);
                }
            }

            if (!$this->getDoctrine()->getManager()->flush()) {
                $result = "success";
            } else {
                $result = "dbfail";
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
        } else if (!$this->auth->isAbsAdmin()) {
            return new JsonResponse([
                'type' => "error",
                'message' => 'You are not an admin'
            ], 401);
        }

        return new JsonResponse([
            'type' => "error",
            'message' => 'Not an AJAX request'
        ], 500);
    }
}

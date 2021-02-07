<?php

namespace App\Controller;

use App\Authentication\Authentication;
use App\Entity\Media;
use App\Entity\Member;
use App\Entity\Project;
use App\Entity\ProjectAdmin;
use App\Form\AddAdminType;
use App\Form\NewProjectType;
use App\Form\ProjectSettingsType;
use App\ProjectCheck\ProjectCheck;
use App\Repository\ProjectAdminRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
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

/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    private $session;
    private $auth;

    public function __construct(SessionInterface $session, UserRepository $userRepository)
    {
        $this->auth = new Authentication($session, $userRepository);
    }

    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request): Response
    {
        $this->session = $session;
        //$auth = new Authentication($session->get('id'));
        //$auth = new Authentication($session, $userRepository);
        if ($this->auth->isAbsAdmin()) {
            $user = $userRepository->find($session->get('id'));

            $allUsers = $userRepository->findAll();
            return $this->render('admin/index.html.twig', [
                'session' => $session,
                'allUsers' => $allUsers
            ]);
        }

        return $this->render('error/401.html.twig', [
            'session' => $session
        ], new Response('', 401));
    }

    /**
     * @Route("/projekty", name="projects")
     */
    public function projects(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request)
    {
        $this->session = $session;

        //$auth = new Authentication($session->get('id'));
        //$auth = new Authentication($session, $userRepository);
        if ($this->auth->isAbsAdmin()) {
            $projects = $projectRepository->findAll();


            return $this->render('admin/projects.html.twig', [
                'session' => $session,
                'projects' => $projects
            ]);
        }

        return $this->render('error/401.html.twig', [
            'session' => $session
        ], new Response('', 401));
    }

    /**
     * @Route("/projekt/{id}", name="project")
     */
    public function project($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request, ProjectAdminRepository $projectAdminRepository)
    {
        $this->session = $session;
        //$auth = new Authentication($session->get('id'));
        //$auth = new Authentication($session, $userRepository);
        $project = $projectRepository->find($id);
        if ($this->auth->isAbsAdmin() && $project != null) {

            $user = $userRepository->find($session->get('id'));
            $basicSettingsForm = $this->createForm(ProjectSettingsType::class, $project);

            $status = "";

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

            $newAdmin = new ProjectAdmin();
            $newAdmin->setProject($project);
            //$addAdminForm = $this->createForm(AddAdminType::class, $newAdmin);
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
                //echo $userdata;
                $newAdminUser = $userRepository->findOneBy(['username' => trim($newAdminUsername)]);
                if (!$projectAdminRepository->findOneBy(['user' => $newAdminUser])) {
                    $newAdmin->setUser($newAdminUser);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newAdmin);
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

        return $this->render('error/401.html.twig', [
            'session' => $session
        ], new Response('', 401));
    }

    /**
     * @Route("/novyprojekt", name="newProject")
     */
    public function newProject(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request)
    {
        $this->session = $session;
        //$auth = new Authentication($session->get('id'));
        //$auth = new Authentication($session, $userRepository);
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

                $userdata = $request->request->get('new_project')['mainAdmin'];

                $newAdminUsername = explode('(', $userdata);
                $newAdminUsername = explode(',', $newAdminUsername[1]);
                $newAdminUsername = $newAdminUsername[0];

                $newAdmin = $userRepository->findOneBy(['username' => $newAdminUsername]);
                $project->setAdmin($newAdmin);

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

        return $this->render('error/401.html.twig', [
            'session' => $session
        ], new Response('', 401));
    }

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
                        array_push($users, $user);
                    }
                }
            } else {
                $users = $usersAll;
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
                $result = "success";
            } else {
                $project->setDeleted(false);
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

    /**
     * @Route("/searchAllUsers", name="searchAllUsers", methods={"POST"})
     */
    public function searchAllUsers(Request $request, ProjectRepository $projectRepository, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest() && $this->auth->isAbsAdmin()) {
            $input = $request->request->get('input');
            $usersAll = $userRepository->searchUser($input);

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
            $response = $serializer->serialize($usersAll, 'json');
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
}

<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\ProjectSettingsType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    private $session;

    /**
     * @Route("/", name="index")
     */
    public function index(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request): Response
    {
        $this->session = $session;
        $user = $userRepository->find($session->get('id'));

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
        } else {
            $response = new Response();
            //$response->headers->set('Content-Type', 'text/plain');
            $response->setStatusCode(403);
            return $response;
        }

        return $this->render('admin/index.html.twig', [
            'session' => $session
        ]);
    }

    /**
     * @Route("/projekty", name="projects")
     */
    public function projects(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request)
    {
        $this->session = $session;
        $projects = $projectRepository->findAll();


        return $this->render('admin/projects.html.twig', [
            'session' => $session,
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/projekt/{id}", name="project")
     */
    public function project($id, SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository, Request $request)
    {
        $this->session = $session;
        $project = $projectRepository->find($id);
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
                        echo 'penis';
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

        return $this->render('admin/project.html.twig', [
            'session' => $session,
            'project' => $project,
            'basicSettingsForm' => $basicSettingsForm->createView(),
            'status' => $status
        ]);
    }
}

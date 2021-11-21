<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Version;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;

class ProjectsController extends AbstractController
{
    
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        
        return $this->render('projects/home.html.twig');
    }
    
    #[Route('/projects', name: 'app_home')]
    public function home(ProjectRepository $projectRepo, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $projectRepo->findAll();
        $projects = $paginator->paginate($data, $request->query->getInt('page', 1), 3);
        return $this->render(
            'projects/index.html.twig',
            compact('projects')
        );
    }

    #[Route("/projects/create", priority: 1, name: 'app_project_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, UserRepository $userRepo): Response
    {
        $project = new Project;
        $version = new Version;
        $version->setVersionNumber('1.');
        $project->addVersion($version);
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {      
            $project->setUser($this->getUser());
            /** @var UploadedFile $brochureFile */
            $poFile = $form->get('poFile')->getData();
            if ($poFile) {
                $originalFilename = pathinfo($poFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $poFile->guessExtension();
                try {
                    $poFile->move(
                        $this->getParameter('poFiles_directory'),
                        $newFilename
                    );               
                } catch (FileException $e) {
                    print_r($e);
                }
                $project->setPoFilename($newFilename);
            }
            $em->persist($project);
            $em->flush();
            $this->addFlash('success', 'Project successfully created!');              
            $filesystem = new Filesystem();
            $newPoFileName = substr($newFilename, 0, -3) . 'toUpdate' . '.' . 'po';
            $filesystem->mkdir('uploads/poFilesToModify');
            $filesystem->copy("uploads/files/{$newFilename}", "uploads/poFilesToModify/{$newPoFileName}");       
            return $this->redirectToRoute('app_home');
        }
        return $this->render('projects/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/projects/{id<\d+>}", name: 'app_project_show')]
    public function show(Project $project): Response
    {
        return $this->render('projects/show.html.twig', compact('project'));
    }

    #[Route("/projects/{id<\d+>}/edit", name: 'app_project_edit')]
    public function edit(project $project, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(projectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Project successfully updated!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('projects/edit.html.twig', ['project' => $project, 'form' => $form->createView()]);
    }

    #[Route("/projects/{id<\d+>}/delete", name: 'app_project_delete')]
    public function delete(project $project, EntityManagerInterface $em, Request $request): Response
    {
        $csrf = $request->request->get('csrf_token');
        if ($this->isCsrfTokenValid('project_deletion' . $project->getId(), $csrf)) {
            $em->remove($project);
            $em->flush();
            $this->addFlash('info', 'Project successfully deleted!');
        }
        return $this->redirectToRoute('app_home');
    }
    
}

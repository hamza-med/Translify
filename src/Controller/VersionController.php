<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Version;
use App\Form\VersionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VersionController extends AbstractController
{
    #[Route('/version', name: 'version')]
    public function index(): Response
    {
        return $this->render('versions/index.html.twig', [
            'controller_name' => 'VersionController',
        ]);
    }
    #[Route('/versions/{id}/create', name: 'app_version_create')]
    public function create(Project $project,Request $request, EntityManagerInterface $em): Response
    {
        $version = new Version;
        $version->setProject($project);
        $form = $this->createForm(VersionType::class, $version);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($version);
            $em->flush();
            $this->addFlash('success', 'Version successfully created!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('versions/create.html.twig', ['form' => $form->createView()]);
    }
    #[Route("/versions/{id<\d+>}", name: 'app_version_show')]
    public function show(version $version): Response
    {
        return $this->render('versions/show.html.twig', compact('version'));
    }

    #[Route("/versions/{id<\d+>}/edit", name: 'app_version_edit')]
    public function edit(version $version, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(versionType::class, $version);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Version successfully updated!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('versions/edit.html.twig', ['version' => $version, 'form' => $form->createView()]);
    }

    #[Route("/versions/{id<\d+>}/delete", name: 'app_version_delete')]
    public function delete(version $version, EntityManagerInterface $em, Request $request): Response
    {
        $csrf = $request->request->get('csrf_token');
        if ($this->isCsrfTokenValid('version_deletion' . $version->getId(), $csrf)) {
            $em->remove($version);
            $em->flush();
            $this->addFlash('info', 'Version successfully deleted!');
        }
        return $this->redirectToRoute('app_home');
    }
}








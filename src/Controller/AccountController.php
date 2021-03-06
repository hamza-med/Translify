<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function show(): Response
    {
        return $this->render('account/show.html.twig');
    }
    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success','Account Updated Successfully');
            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/edit.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/account/change-password', name: 'app_account_change_password')]
    public function changePassword(Request $request, EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder ): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user,$form['newPassword']->getData());
            $user->setPassword($password);
            $em->flush();
            $this->addFlash('success', 'Password Updated Successfully');
            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/change_password.html.twig', ['form' => $form->createView()]);
    }
}

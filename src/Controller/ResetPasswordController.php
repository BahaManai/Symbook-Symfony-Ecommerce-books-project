<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function requestReset(
        Request $request,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        EmailService $emailService
    ): Response {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepo->findOneBy(['email' => $email]);

            if ($user) {
                $token = Uuid::v4();
                $user->setResetToken($token);
                $em->flush();

                $emailService->send(
                    new \Symfony\Component\Mime\Address('aminekilani901@gmail.com', 'BookNest'),
                    $user->getEmail(),
                    'Réinitialisation du mot de passe',
                    'security/reset_email.html.twig',
                    ['user' => $user, 'token' => $token]
                );
            }

            $this->addFlash('success', 'Si cet email est valide, un lien de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/request_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $user = $userRepo->findOneBy(['resetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Token invalide');
        }

        $form = $this->createForm(\App\Form\ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $newPassword));
            $user->setResetToken(null);
            $em->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_form.html.twig', [
            'form' => $form->createView(),
            'token' => $token
        ]);
    }

}

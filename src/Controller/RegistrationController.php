<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mime\Address;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailService $emailService)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setConfirmationToken(bin2hex(random_bytes(32)));
            $user->setIsVerified(false);
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'email de confirmation
            $this->emailService->send(
                new Address('aminekilani901@gmail.com', 'BookNest'),
                $user->getEmail(),
                'BookNest | Mail de confirmation',
                'registration/confirmation_email.html.twig',
                [
                    'user' => $user,
                    'token' => $user->getConfirmationToken(),
                ]
            );

            $this->addFlash('success', 'Inscription réussie ! Veuillez vérifier votre email pour confirmer.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $token = $request->query->get('token');
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('verify_email_error', $translator->trans('Le lien de vérification est invalide ou a expiré.', [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Votre adresse email a été vérifiée.');
        return $this->redirectToRoute('app_login');
    }
}

<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Commande;
use Symfony\Component\Mime\Address;
class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(
        Address $from,
        string $to,
        string $subject,
        string $template,
        array $context
    ): void {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
    public function sendCommandeConfirmation(Commande $commande): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('aminekilani901@gmail.com', 'BookNest'))
            ->to($commande->getUser()->getEmail())
            ->subject('Confirmation de votre commande - ' . $commande->getNumero())
            ->htmlTemplate('Client/commande_confirmation.html.twig')
            ->context([
                'commande' => $commande,
            ]);

        $this->mailer->send($email);
    }

}
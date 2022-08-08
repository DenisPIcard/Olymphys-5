<?php

namespace App\Service;

use App\Entity\Newsletter;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendNewsletterService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(User $user, Newsletter $newsletter): void
    {
        // throw new \Exception('Message non envoyé');

        $email = (new TemplatedEmail())
            ->from('info@olymphys.fr')
            ->to($user->getEmail())
            ->subject('Olympiades de Physique France ' . $newsletter->getName())
            ->htmlTemplate('newsletter/newsletter.html.twig')
            ->context(compact('newsletter', 'user'));
        $this->mailer->send($email);
    }
}
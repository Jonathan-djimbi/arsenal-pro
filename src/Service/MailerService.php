<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
Use Symfony\Component\Mime\Email;

class MailerService
{   private $mailer;
    public function __construct ( MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        $to = 'siteadmin@hotmail.fr',
        $subject = 'This is the Mail subject !',
        $content = '',
        $text = ''
    ): void{
        $email = (new Email())
            ->from('noreply@mysite.com')
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->html($content);
        $this->mailer->send($email);
    }
}
<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Booking;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendBookingDetails(Booking $bookingDto, string $emailAddress): void
    {
        $email = (new Email())
            ->to($emailAddress)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See <b>Twig</b> integration for better HTML integration!</p>');

        $this->mailer->send($email);
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * @param BookingDto[] $bookings
     */
    public function sendBookingDetails(array $bookings, string $emailAddress): void
    {
        $body = "<p>Hello {$bookings[0]->firstName},</p>";
        foreach ($bookings as $booking) {
            $body .= "<h2>Room: {$booking->room} {$booking->unit}</h2>" .
            "<p>Check-In Date: {$booking->checkInDate}</p>" .
            "<p>Check-Out Date: {$booking->checkOutDate}</p>" .
            "<p>First Name: {$booking->firstName}</p>" .
            "<p>Original Referer: {$booking->originalReferer}</p>" .
            "<p>{$booking->passCode}</p>" .
            "<p></p>";
        }
        $email = (new Email())
            ->to($emailAddress)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html($body);

        $this->mailer->send($email);
    }
}

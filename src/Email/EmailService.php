<?php

/**
 * Handles sending emails.
 */

namespace App\Email;

/**
 * Class EmailService
 *
 * @package App\Email
 */
class EmailService
{
    /**
     * @var string reservation confirmation event
     */
    public const EVENT_CONFIRM = 'confirm';

    /**
     * Sends email of given type at user's email address.
     *
     * @param string $event what kind of email
     * @param string $email where to
     * @param array $args optional arguments
     */
    public function send(string $event, string $email, array $args = []): void
    {
    }
}

<?php

/**
 * Handles sending SMS.
 */

namespace App\SMS;

/**
 * Class SMSService
 *
 * @package App\SMS
 */
class SMSService
{
    /**
     * @var string sending new PIN event
     */
    public const EVENT_RESET_PIN = 'reset';

    /**
     * Sends SMS of given type at user's cell phone number.
     *
     * @param string $event what kind of SMS
     * @param string $phone where to
     * @param array $args optional arguments
     */
    public function send(string $event, string $phone, array $args = []): void
    {
    }
}
